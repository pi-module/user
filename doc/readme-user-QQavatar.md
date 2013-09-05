##QQ头像文档##

如果系统支持第三方登录，如QQ登录时:用户使用QQ帐号登录时，系统会调用QQ提供的头像;
而用户切换到其他第三方帐号时，头像也会随之切换对应的第三方头像;
退出状态下，用户头像不再使用第三方帐号的头像;用户直接使用网站帐号而非第三方帐号登录时，则调用用户自己选择的头像



###QQ头像的官方使用文档：###
http://wiki.open.qq.com/wiki/%E5%89%8D%E7%AB%AF%E9%A1%B5%E9%9D%A2%E8%A7%84%E8%8C%83

目前头像只能通过 OpenAPI（例如 v3/user/get_info ，v3/user/get_multi_info）的方式来获取到用户头像的 URL。
需要注意的是，在应用上线前，接口返回的头像 URL 有防盗链。上线后，才会开放防盗链的限制。 

####空间和朋友平台头像有不同的尺寸：####
- 空间头像尺寸有：100px，50px，30px 3 种规格。
- 朋友头像尺寸有：100px，60px，30px 3 种规格。

通过 OpenAPI（例如 v3/user/get_info ，v3/user/get_multi_info）获取到的头像地址（即返回包中的 figureurl）通常是如下格式：
http:// 头像域名 /[campus/qzopenapp]/ 加密串 / 尺寸 
开发者得到上述地址后，修改后面的尺寸数字即可，如下面例子所示： 
获取到的 figureurl 为： 
http://qlogo3.store.qq.com/qzopenapp/d8219673598dbd6f00000d307e46c7bde4cfffca38933abc5a4ecac43bc03e44/100 
如果你想的到 30px 尺寸的头像，只要把尺寸改为 30 即可：
http://qlogo3.store.qq.com/qzopenapp/d8219673598dbd6f00000d307e46c7bde4cfffca38933abc5a4ecac43bc03e44/30 
