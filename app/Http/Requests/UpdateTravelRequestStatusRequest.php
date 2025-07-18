<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTravelRequestStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        $travelRequest = $this->route('travelRequest');

        return $travelRequest->user_id !== $user->id;
    }

    public function failedAuthorization()
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'Você não pode alterar o status de uma solicitação criada por você mesmo.'
        );
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in(['approved', 'cancelled'])
            ],
            'cancellation_reason' => 'required_if:status,cancelled|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser "approved" ou "cancelled".',
            'cancellation_reason.required_if' => 'A razão do cancelamento é obrigatória quando o status for "cancelled".',
        ];
    }
}
