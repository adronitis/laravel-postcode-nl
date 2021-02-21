<?php

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Speelpenning\PostcodeNl\Exceptions\AccountSuspended;
use Speelpenning\PostcodeNl\Exceptions\AddressNotFound;
use Speelpenning\PostcodeNl\Exceptions\Unauthorized;
use Speelpenning\PostcodeNl\Services\AddressLookup;

class AddressLookupTest extends TestCase
{
    public function testCredentialsAreSet()
    {
        $auth = config('postcode-nl.requestOptions.auth');

        $this->assertNotEmpty(Arr::get($auth, 0));
        $this->assertNotEmpty(Arr::get($auth, 1));
    }

    public function testInvalidCredentialsThrowUnauthorized()
    {
        $this->expectException(Unauthorized::class);

        config([
            'postcode-nl.requestOptions.auth' => [
                'invalid', 'credentials'
            ]
        ]);

        $lookup = app(AddressLookup::class);
        $lookup->lookup('1000AA', 1);
    }

    public function testSuspendedCredentialsThrowAccountSuspended()
    {
        $this->expectException(AccountSuspended::class);

        config([
            'postcode-nl.requestOptions.auth' => [
                'H2E4y1m7elD6gt73vTCjw9tWwayV8eUHrpBv1XpOfTw', 'LNW0bPOWn0qHc2iSDDv8NifUQlucgfehFcSHyix0kyt'
            ]
        ]);

        $lookup = app(AddressLookup::class);
        $lookup->lookup('1000AA', 1);
    }

    public function testExistingAddressReturnsAnAddress()
    {
        $lookup = app(AddressLookup::class);
        $address = $lookup->lookup('1000AA', 1);

        $this->assertInstanceOf(Speelpenning\PostcodeNl\Address::class, $address);
    }

    public function testNonExistingAddressThrowsAddressNotFound()
    {
        $this->expectException(AddressNotFound::class);

        $lookup = app(AddressLookup::class);
        $lookup->lookup('9999ZZ', 99999);
    }

    public function testInvalidLookupThrowsValidationException()
    {
        $this->expectException(ValidationException::class);

        $lookup = app(AddressLookup::class);
        $lookup->lookup('invalid', 12345);
    }
}
