<script type="text/javascript">
    function scroll_news() {
        var firstNode = $('#actor li'); //获取li对象
        firstNode.eq(0).slideUp("slow", function () { //获取li的第一个,执行fadeOut,并且call - function.
            $(this).clone().appendTo($(this).parent()).show(); //把每次的li的第一个 克隆，然后添加到父节点 对象。
            $(this).remove();//最后  把每次的li的第一个去掉。
        });//注意哦,这些都是在fadeOut里面的callback函数理执行的。
    }
    setInterval('scroll_news()', 2500);//每隔0.5秒，执行scroll_news()
</script>
<div class="ym">
    <div class="ym1">
        <div id=imgPlay>
            <ul class="imgs" id="actor" style="overflow: hidden;">
                <li>
                    <IMG src="/i/web/images/b1.jpg"/>
                </li>
                <li>
                    <IMG src="/i/web/images/b2.jpg"/>
                </li>
                <li>
                    <IMG src="/i/web/images/b3.jpg"/>
                </li>
            </ul>
            <DIV class=prev></DIV>
            <DIV class=next></DIV>
        </DIV>
        <div class="ym1_1"></div>
        <div class="ym1_2">
            <form action="http://www.scoyey.com" id="searchform" method="get">
                <label for="s" class="screen-reader-text"><img src="/i/web/images/sous.jpg"/></label>
                <input type="text" id="s" name="s" value=""/>
                <input type="submit" value="" id="searchsubmit"/>
            </form>
        </div>
    </div>
    <div class="ym2">
        <div class="ym2_1">
            <div class="ym2_2">
                <div class="a1">
                    <div class="a1_1">
                        <img src="/i/web/images/tu1.jpg"/>
                    </div>

                </div>
                <div class="a2">
                    <h1>关于我们</h1>
                    <b>About us</b>
                    <p>
                        <?= mb_substr(strip_tags($info['class_content']),0, 150,"utf-8") ?>
                    </p>
                    <div class="a2_1"><a href=" <?= url('main', 'about', ['cid'=>1]) ?> " target="_blank">— Click to view
                            more</a></div>
                </div>
            </div>
            <div class="ym2_3">
                <div class="b1">
                    <div class="b1_1">
                        <img src="/i/web/images/tu3.jpg"/>
                    </div>

                </div>
                <div class="b2">
                    <h1>新闻动态</h1>
                    <b>News</b>
                    <div class="b2_2">

                        <ul>
                            <?php foreach ($item as $new) { ?>

                                <div class="b2_3"><a href="<?= url('main', 'info', ["id" => $new['id']]) ?>"
                                                     target="_blank">
                                        <?= $new['n_name'] ?>
                                        ...</a></div>
                                <div class="b2_4"><?=$new['n_time'] ?></div>
                            <?php } ?>
                    </div>
                    <div class="b2_1"><a href="<?= url('main', 'news',['cid'=>4]) ?>" target="_blank">— Click
                            to view more</a></div>
                </div>
            </div>
            <div class="ym2_4">
                <div class="c1">
                    <div class="c1_1">
                        <?= $config['site_tel'] ?>
                    </div>
                </div>
                <h1>联系我们</h1>
                <b>Contact us</b>
                <p>联系地址：<?=$config['site_address'] ?>
                    <br/>电话：<?= $config['site_tel'] ?>
                    <br/>联系人：<?=$config["site_Person"] ?>
                    <br/>Email：<?=$config['site_mail'] ?>
                    <br/>
                </p>

            </div>
        </div>

    </div>
</div>
</div>

