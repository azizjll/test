<?php

namespace App\Validator;

use Symfony\Component\Validator\NoBadWordsConstraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use App\Service\BadWordsService; // Import the service

class NoBadWordsValidator extends ConstraintValidator
{
    private $badWordsService;

    public function __construct(BadWordsService $badWordsService) // Inject the service
    {
        $this->badWordsService = $badWordsService;
    }

    

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof NoBadWordsConstraint) {
            throw new UnexpectedTypeException('constraint', NoBadWordsConstraint::class, $constraint);
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException('value', 'string', $value);
        }

        foreach ($this->badWordsList as $badWord) {
            // Regular expression to match the bad word case-insensitively
            $pattern = '/\b' . preg_quote($badWord, '/') . '\b/i';
            if (preg_match($pattern, $value)) {
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
                return;
            }
        }
    }
}