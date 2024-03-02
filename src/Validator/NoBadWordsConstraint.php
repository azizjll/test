<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NoBadWordsConstraint extends Constraint
{
    public $message = 'Votre annonce contient des mots interdits.';
}
