<?php

namespace App\Modules\Consultas\Requests;

use App\Modules\Shared\Requests\BaseFormRequest;

/**
 * Filtros compartidos por las consultas de solo lectura.
 *
 * Un único request evita repetir reglas en cada endpoint y mantiene un tope
 * de página para que una consulta masiva accidental no bloquee la API.
 */
class ConsultaRequest extends BaseFormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'per_page' => 'sometimes|integer|min:1|max:50',
            'especialidad' => 'sometimes|string|max:100',
            'tipo_instructor' => 'sometimes|in:planta,contratista',
            'ambiente_id' => 'sometimes|integer|exists:environments,id',
            'usuario_id' => 'sometimes|integer|exists:users,id',
            'solo_vigentes' => 'sometimes|boolean',
            'tipo' => 'sometimes|in:daño,perdida,falla,otro',
            'estado' => 'sometimes|string|max:30',
            'aprendiz_id' => 'sometimes|integer|exists:apprentices,id',
            'ficha_id' => 'sometimes|integer|exists:coursegroups,id',
            'desde' => 'sometimes|date',
            'hasta' => 'sometimes|date|after_or_equal:desde',
        ];
    }
}
