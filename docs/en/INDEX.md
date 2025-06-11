# UUID

You can add the Extension to any dataobject and this will give it a globally unique hash (UUID).

# Important

Because we are using upper and lower case letters, the UUID is case sensitive.

By default, Mysql is NOT case casensitive, so you need to make sure that you use a case sensitive collation for the UUID field.

This is how you do that:

```php
$match = MyObject::get()->filter(['UUID:case' => 'sensitiveCode'])->first();
```

# config.yml

```yml
MyDataObject:
    extensions:
        - Sunnysideup\UUID\Extensions\UUIDExtension

# or ...

MyPage:
    extensions:
        - Sunnysideup\UUID\Extensions\UUIDExtension
```

Now your dataobject have two extra fields: `UUID` and `PublicUUID`.

`UUID` is 65 characters long and is extremely likely to be unique.
`PublicUUID` is only 12 characters long (so it might not be unique!)

# customisation

You can use the following methods in your dataobject wit the `UUIDExtension` to customise the CMS:

-   `ShowUUIDInCMS` - returns a boolean - show the fields in the CMS
-   `UUIDTabInCMS` - returns a string - e.g. 'Root.Security'
