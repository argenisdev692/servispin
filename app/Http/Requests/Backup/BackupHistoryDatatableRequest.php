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
        $this->validate($this->rules());

        return $this->all();
    }
}
