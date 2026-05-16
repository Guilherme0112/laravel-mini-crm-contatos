<?php

namespace App\Core\Application\Contact\DTOs;

use App\Core\Domain\Contact\Entities\Contact;

class ContactData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $phone
    ) {
    }

    public function toEntity(?int $id = null): Contact
    {
        if ($id === null) {
            return Contact::create($this->name, $this->email, $this->phone);
        }

        return new Contact(
            $id,
            $this->name,
            new \App\Core\Domain\Contact\ValueObjects\EmailAddress($this->email),
            new \App\Core\Domain\Contact\ValueObjects\PhoneNumber($this->phone)
        );
    }
}
