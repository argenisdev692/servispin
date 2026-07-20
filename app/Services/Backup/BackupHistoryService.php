<?php

namespace App\Services\Backup;

use App\DataTransferObjects\Backup\BackupFileDetailsData;
use App\DataTransferObjects\Backup\BackupHistoryRowData;
use App\Models\Backup\BackupFile;
use App\Models\User;
use App\Repositories\Backup\BackupHistoryRepositoryInterface;
use App\Support\Backup\BackupFileSizeFormatter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class BackupHistoryService
{
    public function __construct(
        private readonly BackupHistoryRepositoryInterface $repository,
        private readonly BackupActivityLogService $activityLogService,
    ) {}

    /**
     * @param  array<string, mixed>  $parameters
     * @return array{draw: int, recordsTotal: int, recordsFiltered: int, data: array<int, array<string, mixed>>}
     */
    public function getDatatableData(User $user, array $parameters): array
    {
        $draw = (int) ($parameters['draw'] ?? 0);
        $start = max(0, (int) ($parameters['start'] ?? 0));
        $length = max(1, min(100, (int) ($parameters['length'] ?? 10)));
        $search = trim((string) ($parameters['search']['value'] ?? ''));
        $orderColumn = (int) ($parameters['order'][0]['column'] ?? 0);
        $orderDirection = strtolower((string) ($parameters['order'][0]['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        $rows = $this->repository->all()->map(
            fn (BackupFile $backupFile): BackupHistoryRowData => $this->mapRow($user, $backupFile)
        );

        $recordsTotal = $rows->count();

        if ($search !== '') {
            $rows = $rows->filter(
                static function (BackupHistoryRowData $row) use ($search): bool {
                    $needle = mb_strtolower($search);

                    return str_contains(mb_strtolower($row->filename), $needle)
                        || str_contains(mb_strtolower($row->disk), $needle)
                        || str_contains(mb_strtolower($row->status), $needle);
                }
            );
        }

        $rows = $this->sortRows($rows, $orderColumn, $orderDirection)->values();
        $recordsFiltered = $rows->count();
        $paginatedRows = $rows->slice($start, $length)->values();

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $paginatedRows
                ->map(static fn (BackupHistoryRowData $row): array => $row->toArray())
                ->all(),
        ];
    }

    public function getDetails(User $user, BackupFile $backupFile): BackupFileDetailsData
    {
        $this->activityLogService->logViewed($user, $backupFile);

        return $this->mapDetails($backupFile);
    }

    public function download(User $user, BackupFile $backupFile): StreamedResponse
    {
        if (! $this->repository->exists($backupFile)) {
            abort(404, 'Backup file not found.');
        }

        $this->activityLogService->logDownloaded($user, $backupFile);

        return Storage::disk($backupFile->disk)->download($backupFile->path, $backupFile->filename);
    }

    public function delete(User $user, BackupFile $backupFile): void
    {
        if (! $this->repository->delete($backupFile)) {
            abort(404, 'Backup file not found.');
        }

        $this->activityLogService->logDeleted($user, $backupFile);
    }

    public function logIndexVisit(User $user): void
    {
        $this->activityLogService->logListed($user);
    }

    private function mapRow(User $user, BackupFile $backupFile): BackupHistoryRowData
    {
        $status = $this->resolveStatus($backupFile);
        $routeKey = $backupFile->getRouteKey();

        return new BackupHistoryRowData(
            id: $backupFile->id,
            formattedDate: $backupFile->createdAt->format('d/m/Y H:i:s'),
            filename: $backupFile->filename,
            formattedSize: BackupFileSizeFormatter::format($backupFile->sizeInBytes),
            disk: $backupFile->disk,
            status: $status['label'],
            statusBadgeClass: $status['badge_class'],
            createdAgo: $backupFile->createdAt->diffForHumans(),
            showUrl: route('admin.backup-history.show', ['backupFile' => $routeKey]),
            downloadUrl: route('admin.backup-history.download', ['backupFile' => $routeKey]),
            destroyUrl: route('admin.backup-history.destroy', ['backupFile' => $routeKey]),
            canDownload: $user->can('download', $backupFile),
            canDelete: $user->can('delete', $backupFile),
            exists: $backupFile->exists,
        );
    }

    private function mapDetails(BackupFile $backupFile): BackupFileDetailsData
    {
        $status = $this->resolveStatus($backupFile);

        return new BackupFileDetailsData(
            id: $backupFile->id,
            filename: $backupFile->filename,
            disk: $backupFile->disk,
            path: $backupFile->path,
            formattedSize: BackupFileSizeFormatter::format($backupFile->sizeInBytes),
            formattedDate: $backupFile->createdAt->format('d/m/Y H:i:s'),
            createdAgo: $backupFile->createdAt->diffForHumans(),
            status: $status['label'],
            statusBadgeClass: $status['badge_class'],
            exists: $backupFile->exists,
        );
    }

    /**
     * @return array{label: string, badge_class: string}
     */
    private function resolveStatus(BackupFile $backupFile): array
    {
        if ($backupFile->isMissing()) {
            return [
                'label' => 'Missing',
                'badge_class' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
            ];
        }

        return [
            'label' => 'Available',
            'badge_class' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        ];
    }

    /**
     * @param  Collection<int, BackupHistoryRowData>  $rows
     * @return Collection<int, BackupHistoryRowData>
     */
    private function sortRows(Collection $rows, int $orderColumn, string $orderDirection): Collection
    {
        $sorter = match ($orderColumn) {
            0 => static fn (BackupHistoryRowData $row): string => $row->formattedDate,
            1 => static fn (BackupHistoryRowData $row): string => $row->filename,
            2 => static fn (BackupHistoryRowData $row): string => $row->formattedSize,
            3 => static fn (BackupHistoryRowData $row): string => $row->disk,
            4 => static fn (BackupHistoryRowData $row): string => $row->status,
            5 => static fn (BackupHistoryRowData $row): string => $row->createdAgo,
            default => static fn (BackupHistoryRowData $row): string => $row->formattedDate,
        };

        $sorted = $orderDirection === 'asc'
            ? $rows->sortBy($sorter)
            : $rows->sortByDesc($sorter);

        return $sorted->values();
    }
}
