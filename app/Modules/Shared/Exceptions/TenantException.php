<?php

namespace App\Modules\Shared\Exceptions;

use App\Modules\Shared\Contracts\RendersInertiaErrorInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class TenantException extends RuntimeException implements RendersInertiaErrorInterface
{
    private function __construct(
        string $message,
        private readonly int $httpStatus,
        private readonly string $userFacingMessage,
    ) {
        parent::__construct($message);
    }

    /**
     * El host de la request no corresponde a ningún tenant registrado.
     */
    public static function tenantNotFound(string $host): self
    {
        return new self(
            message: "No tenant found for host [{$host}].",
            httpStatus: Response::HTTP_NOT_FOUND, //404
            userFacingMessage: 'No pudimos encontrar el espacio de trabajo que buscas.',
        );
    }

    /**
     * El tenant existe, pero su estado no es "active".
     */
    public static function tenantSuspended(): self
    {
        return new self(
            message: 'Tenant is suspended.',
            httpStatus: Response::HTTP_FORBIDDEN, //403
            userFacingMessage: 'Este espacio de trabajo está suspendido. Contacta a tu administrador.',
        );
    }

    /**
     * La request no trae un Host válido (caso extremo, casi nunca ocurre).
     */
    public static function tenantHostMissing(): self
    {
        return new self(
            message: 'Request host is missing or empty.',
            httpStatus: Response::HTTP_BAD_REQUEST, //400
            userFacingMessage: 'No pudimos determinar el espacio de trabajo de esta solicitud.',
        );
    }

    public static function tenantMismatch(): self
    {
        return new self(
            message: 'Tenants User not match with domain tenant.',
            httpStatus: Response::HTTP_FORBIDDEN, //403
            userFacingMessage: 'No pudimos determinar el espacio de trabajo de esta solicitud.',
        );
    }


    /**
     * Error de programación, no de negocio: se consultó un modelo
     * tenant-aware sin que TenantManager tuviera un tenant activo.
     * Debe ser imposible en producción si el middleware/jobs están
     * bien configurados — si aparece, es un bug nuestro, no del usuario.
     */
    public static function contextNotSet(string $model): self
    {
        return new self(
            message: "Tenant context is not set while querying [{$model}]. "
            . "Ensure TenantMiddleware ran or the tenant was restored in this job.",
            httpStatus: Response::HTTP_INTERNAL_SERVER_ERROR, //500
            userFacingMessage: 'Ocurrió un error inesperado.',
        );
    }

    public function status(): int
    {
        return $this->httpStatus;
    }

    public function userMessage(): string
    {
        return $this->userFacingMessage;
    }
}
