﻿<?php
// 运行 php converter.php
$db = new mysqli();
// 根据实际情况更改
$db->connect('localhost','数据库用户名','数据库密码','数据库名称');
$prefix = 'typecho_';
$sql = <<<TEXT
select title,text,created,category,tags from {$prefix}contents c,
 (select cid,group_concat(m.name) tags from {$prefix}metas m,{$prefix}relationships r where m.mid=r.mid and m.type='tag' group by cid ) t1,
(select cid,m.name category from {$prefix}metas m,{$prefix}relationships r where m.mid=r.mid and m.type='category') t2
where t1.cid=t2.cid and c.cid=t1.cid
TEXT;
$res = $db->query($sql);
if ($res) {
    if ($res->num_rows > 0) {
        while ($r = $res->fetch_object()) {
            $_c = @date('Y-m-d H:i:s', $r->created);
            $_t = str_replace('<!--markdown-->', '', $r->text);
            $_tmp = <<<TMP
{$_t}
TMP;
            // windows下把文件名从UTF-8编码转换为GBK编码，避免出现生成的文件名为乱码的情况
            if (strpos(PHP_OS, "WIN") !== false) {
                $name = iconv("UTF-8", "GBK//IGNORE", $r->title);
                echo $name.'<br>';
            } else {
                $name = $r->title;
				echo $name.'<br>';
            }
            // 替换不合法文件名字符
            file_put_contents(str_replace(array(" ", "?", "\\", "/", ":", "|", "*"), '-', $name) . ".md", $_tmp);
        }
    }
    $res->free();
}
$db->close();