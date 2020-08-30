# Ulrack Persistent Extension - Use a persistent storage

After a storage is created. It can be accessed through the services layer.
Simply add a reference to the storage in a service to load it. E.g.
`@{persistent.my-persistent-storage}`. If it is used as a parameter, then it
can be type-hinted with the interface `GrizzIt\Storage\Common\StorageInterface`.

## Further reading

[Back to usage index](index.md)

[Installation](installation.md)

[Create a persistent storage](create-a-persistent-storage.md)
