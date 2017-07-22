## image模組

---
### 上傳
```
Image::upload($檔案, $對像?, $其他?)
```
---

### 熊貓縮圖功能
#### 使用
1. 請至 https://tinypng.com/developers 申請，取得 API Key
2. 在 .env 加上 TINYPNG_KEY={API Key} 即可
3. 開發可用: TINYPNG_KEY=b4jYsFptDKC1vRpSur0n6bei9RskV0I1

#### 存放位置
1. 原圖：./storage/app/images/origin/
2. 壓縮圖：./storage/app/images/
3. 小圖： ./storage/app/images/thumbnail/ 

#### 讀取
讀取圖片時，可在路由後面加入參數，選擇要讀取哪種圖片。預設讀取壓縮過的圖，如果找不到圖片會自動讀取 origin 內的原圖

1. 壓縮：http://dns/image/realname
2. 原圖：http://dns/image/realname/origin
3. 小圖：http://dns/image/realname/thumbnail