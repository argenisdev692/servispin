<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backup\BackupHistoryDatatableRequest;
use App\Models\Backup\BackupFile;
use App\Services\Backup\BackupHistoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class BackupHistoryController extends Controller
{
    public function __construct(
        private readonly BackupHistoryService $backupHistoryService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', BackupFile::class);

        $user = $request->user();
        if ($user !== null) {
            $this->backupHistoryService->logIndexVisit($user);
        }

        return view('admin.backup-history.index');
    }

    public function datatable(BackupHistoryDatatableRequest $request): JsonResponse
    {
        $this->authorize('viewAny', BackupFile::class);

        $user = $request->user();
        if ($user === null) {
            abort(403);
        }

        return response()->json(
            $this->backupHistoryService->getDatatableData($user, $request->datatablePayload())
        );
    }

    public function show(Request $request, BackupFile $backupFile): View
    {
        $this->authorize('view', $backupFile);

        $user = $request->user();
        if ($user === null) {
            abort(403);
        }

        $details = $this->backupHistoryService->getDetails($user, $backupFile);

        return view('admin.backup-history.show', [
            'details' => $details,
            'backupFile' => $backupFile,
        ]);
    }

    public function download(Request $request, BackupFile $backupFile): StreamedResponse
    {
        $this->authorize('download', $backupFile);

        $user = $request->user();
        if ($user === null) {
            abort(403);
        }

        return $this->backupHistoryService->download($user, $backupFile);
    }

    public function destroy(Request $request, BackupFile $backupFile): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $backupFile);

        $user = $request->user();
        if ($user === null) {
            abort(403);
        }

        $this->backupHistoryService->delete($user, $backupFile);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Backup deleted successfully.',
            ]);
        }

        return redirect()
            ->route('admin.backup-history.index')
            ->with('success', 'Backup deleted successfully.');
    }
}
