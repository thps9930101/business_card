## 光陣三維3D系統
# 主機需要安裝以下套件



| 套件名稱 | 用途 |
| -------- | -------- |
| php-imagick | 用於處理上傳的canvas 縮圖 |

```
sudo apt-get update
sudo apt-get install php-imagick

```

-----------------------------------------------------------------------------------------------------

| 套件名稱 | 用途 |
| -------- | -------- |
| ffmpeg | 用於處理上傳的影片cover |

```

這個apt install 會安裝到舊版的ffmpeg所以直接去官網下載最新版的

```
wget https://johnvansickle.com/ffmpeg/builds/ffmpeg-git-amd64-static.tar.xz
wget https://johnvansickle.com/ffmpeg/builds/ffmpeg-git-amd64-static.tar.xz.md5
```

上面兩個指令跑完後就可以直接驗證

```
md5sum -c ffmpeg-git-amd64-static.tar.xz.md5
```

應該要出現
ffmpeg-git-amd64-static.tar.xz: OK

照理來說剩下的就是把bin檔放到/usr/local/bin/底下就好了
```
sudo mv 安裝資料夾/ffmpeg 安裝資料夾/ffprobe /usr/local/bin/
```
詳情請見下面這邊
https://www.johnvansickle.com/ffmpeg/faq/

然後要安裝php 的套件，不過這個在composer上面就有了，所以直接composer install就好了


aaa