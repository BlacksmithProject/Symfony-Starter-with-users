<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Http;

use App\Security\Infrastructure\Http\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController
{
    public function badRequestResponse(ValidationException $exception): JsonResponse
    {
        return new JsonResponse($exception->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    public function unprocessableEntity(\Exception $exception): JsonResponse
    {
        return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
