# 用 PHP 實現 [MessagePack](https://msgpack.org/)

## 執行方式

- Docker
```bash
docker build --tag msgpack $REPO

docker run --rm msgpack $JSON
```

- 直接執行, 最少 PHP 8.0
```bash
php packer.php $JSON
```
