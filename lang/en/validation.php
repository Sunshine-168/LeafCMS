<?php

return [
    'required' => 'The :attribute field is required.',
    'string' => 'The :attribute field must be text.',
    'email' => 'Enter a valid email address for :attribute.',
    'url' => 'Enter a valid URL for :attribute.',
    'integer' => 'The :attribute field must be an integer.',
    'numeric' => 'The :attribute field must be a number.',
    'boolean' => 'The :attribute field must be true or false.',
    'array' => 'The :attribute field must be a list.',
    'in' => 'The selected :attribute is invalid.',
    'exists' => 'The selected :attribute does not exist.',
    'unique' => 'The :attribute is already in use.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'date' => 'Enter a valid date for :attribute.',
    'after' => 'The :attribute must be after :date.',
    'image' => 'The :attribute must be an image.',
    'file' => 'The :attribute must be a file.',
    'mimes' => 'The :attribute must have one of these file types: :values.',
    'max' => ['string' => 'The :attribute must contain at most :max characters.', 'numeric' => 'The :attribute must be at most :max.', 'file' => 'The :attribute must be at most :max KB.', 'array' => 'The :attribute must contain at most :max items.'],
    'min' => ['string' => 'The :attribute must contain at least :min characters.', 'numeric' => 'The :attribute must be at least :min.', 'file' => 'The :attribute must be at least :min KB.', 'array' => 'The :attribute must contain at least :min items.'],
    'attributes' => [],
];
