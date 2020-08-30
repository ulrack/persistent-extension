# Ulrack Persistent Extension - Create a persistent storage

The persistent storage can be used to store the state of an application on a
specific machine. To register a storage, simply create a file in
`configuration/persistent`. Add the following content:
```json
{
    "my-persistent-storage": {}
}
```

The key of the storage will be the name of the storage. The value associated
with the key is the default value of the storage. When the storage is requested
and it does not exist yet, the default will be used instead. Whenever it is
requested, it will be stored in the factory untill the application finishes.
After that, the storage will be saved, with the latest value.

## Further reading

[Back to usage index](index.md)

[Installation](installation.md)

[Use a persistent storage](use-a-persistent-storage.md)
