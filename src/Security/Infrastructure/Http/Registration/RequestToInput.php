<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Http\Registration;

use App\Security\Domain\Exception\EmailIsInvalidOrAlreadyTaken;
use App\Security\Domain\Exception\PasswordIsTooShort;
use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\Password;
use App\Security\Infrastructure\Http\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;

final class RequestToInput
{
    /**
     * @throws ValidationException
     * @throws EmailIsInvalidOrAlreadyTaken
     * @throws PasswordIsTooShort
     */
    public static function convert(Request $request): Input
    {
        $errors = self::validate($request);

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $input = new Input();

        $input->email = new Email($request->request->get('email'));
        $input->password = new Password($request->request->get('password'));

        return $input;
    }

    private static function validate(Request $request): array
    {
        $errors = [];
        if ($request->request->has('email') === false) {
            $errors['email'] = 'missing field';
        }
        if (is_string($request->request->get('email')) === false) {
            $errors['email'] = 'email should be a string';
        }

        if ($request->request->has('password') === false) {
            $errors['password'] = 'missing field';
        }
        if (is_string($request->request->get('password')) === false) {
            $errors['password'] = 'password should be a string';
        }
        return $errors;
    }
}
