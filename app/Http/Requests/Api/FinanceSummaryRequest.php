<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|\Illuminate\Validation\Rules\In>>
     */
    public function rules(): array
    {
        return [
            'period' => ['nullable', Rule::in(['all', 'day', 'week', 'month', 'custom'])],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'channel' => ['nullable', 'string', 'max:60'],
            'group_by' => ['nullable', Rule::in(['none', 'channel'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'period' => $this->input('period', 'all'),
            'group_by' => $this->input('group_by', 'none'),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $period = (string) $this->input('period', 'all');
            $fromDate = $this->input('from_date');
            $toDate = $this->input('to_date');

            if ($period === 'custom' && (! $fromDate || ! $toDate)) {
                $validator->errors()->add('period', 'from_date and to_date are required when period is custom.');
            }

            if ($period !== 'custom' && ($fromDate || $toDate)) {
                $validator->errors()->add('period', 'from_date and to_date can only be used with period=custom.');
            }
        });
    }
}
