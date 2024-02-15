# Agenda
## これはなに？
open api (swagger) のyaml定義からResourceファイルを生成するツールです。

## Requirement
- php
- composer
- swagger-merger
### phpのインストール
TODO
### composerのインストール
TODO
### swagger-mergerのインストール
nodeのインストールが必要です。
```shell
npm install -g swagger-merger
```

## 使い方
1. swagger-mergerで生成元のindex.yamlを統合する
```shel
swagger-merger -i ../rpst-oms-backend/doc/spec/api/admin/hoge/index.yaml -o target.yaml 
```
2. 以下コマンドを実行する
```shell
php main2.php 
```
3. Resourceファイル群が生成されます

# TODO
- [ ] Providerクラスの生成
  - GETリクエストのparametersのパース
- [ ] swagger-mergerコマンドの内包
- [ ] yamlファイルを引数で指定できるようにする