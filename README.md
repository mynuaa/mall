#mall

## 安装
    1. 复制config.php.sample 为config.php

    2. 针对里面内容进行修改
        主要修改数据库密码，
        'UC_DBTABLEPRE',如果自己额外默认安装好的ucenter，则是 '`ucenter`.uc_'

    3. 然后执行initialize.sh

    4. 在数据库中新建mall数据库，并导入项目中的app_zfjmall.sql


    5. mall配置比较简单，一般sso配置好就没什么大问题