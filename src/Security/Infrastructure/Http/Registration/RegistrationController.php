<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Http\Registration;

use App\Security\Domain\Exception\EmailIsInvalidOrAlreadyTaken;
use App\Security\Domain\Exception\PasswordIsTooShort;
use App\Security\Domain\Exception\TokenNotFound;
use App\Security\Domain\UseCase\Registration;
use App\Security\Infrastructure\Http\AbstractController;
use App\Security\Infrastructure\Http\Exception\ValidationException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RegistrationController extends AbstractController
{
    private Registration $registration;
    private LoggerInterface $logger;

    public function __construct(
        Registration $registration,
        LoggerInterface $logger
    ) {
        $this->registration = $registration;
        $this->logger = $logger;
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $input = RequestToInput::convert($request);

            $user = $this->registration->execute($input->email, $input->password);

            return new JsonResponse($user, Response::HTTP_CREATED);
        } catch (ValidationException $exception) {
            return $this->badRequestResponse($exception);
        } catch (EmailIsInvalidOrAlreadyTaken|PasswordIsTooShort $exception) {
            return $this->unprocessableEntity($exception);
        } catch (TokenNotFound $exception) {
            // Should NEVER happen
            $this->logger->critical('Token not found on registration', [
                'trace' => $exception->getTraceAsString(),
            ]);

            return new JsonResponse(null, Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}
