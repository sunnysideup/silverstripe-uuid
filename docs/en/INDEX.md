UUID
===============================================

YOu can add the Extension to any dataobject and this will give it a globally unique hash (UUID).


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
 - `ShowUUIDInCMS` - returns a boolean
 - `UUIDTabInCMS` - returns a string - e.g. 'Root.Security'
