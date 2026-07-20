<?php

namespace App\Http\Requests\Backup;

use App\Models\Backup\BackupFile;
use Illuminate\Foundation\Http\FormRequest;

final class BackupHistoryDatatableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', BackupFile::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'draw' => ['nullable', 'integer'],
            'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'array'],
            'search.value' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'array'],
            'order.*.column' => ['nullable', 'integer', 'min:0', 'max:6'],
            'order.*.dir' => ['nullable', 'in:asc,desc'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function datatablePayload(): array
    {
        return [
            'draw' => (int) $this->input('draw', 0),
            'start' => max(0, (int) $this->input('start', 0)),
            'length' => max(1, min(100, (int) $this->input('length', 10))),
            'search' => [
                'value' => (string) $this->input('search.value', $this->input('search', [])['value'] ?? ''),
            ],
            'order' => [
                [
                    'column' => (int) $this->input('order.0.column', 0),
                    'dir' => $this->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc',
                ],
            ],
        ];
    }
}
