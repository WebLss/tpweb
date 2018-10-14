<?php
namespace Home\Controller;
use Common\Controller\HomeBaseController;
/**
 * 网站首页
 */
class IndexController extends HomeBaseController {

    // 首页
    public function index(){
        $articles=D('Article')->getPageData();
        $assign=array(
            'articles'=>$articles['data'],
            'page'=>$articles['page'],
            'cid'=>'index'
            );
        $this->assign($assign);
        $this->display();
    }

    // 分类
    public function category(){
        $cid=I('get.cid',0,'intval');
        // 获取分类数据
        $category=D('Category')->getDataByCid($cid);
        // 如果分类不存在；则返回404页面
        if (empty($category)) {
            header("HTTP/1.0  404  Not Found");
            $this->display('./Template/default/Home/Public/404.html');
            exit(0);
        }
        // 获取分类下的文章数据
        $articles=D('Article')->getPageData($cid);
        $assign=array(
            'category'=>$category,
            'articles'=>$articles['data'],
            'page'=>$articles['page'],
            'cid'=>$cid
            );
        $this->assign($assign);
        $this->display();
    }

    // 标签
    public function tag(){
        $tid=I('get.tid',0,'intval');
        // 获取标签名
        $tname=D('Tag')->getFieldByTid($tid,'tname');
        // 如果标签不存在；则返回404页面
        if (empty($tname)) {
            header("HTTP/1.0  404  Not Found");
            $this->display('./Template/default/Home/Public/404.html');
            exit(0);
        }
        // 获取文章数据
        $articles=D('Article')->getPageData('all',$tid);
        $assign=array(
            'articles'=>$articles['data'],
            'page'=>$articles['page'],
            'title'=>$tname,
            'title_word'=>'拥有<span class="b-highlight">'.$tname.'</span>标签的文章',
            'cid'=>'index'
            );
        $this->assign($assign);
        $this->display();
    }

    // 文章内容
    public function article(){
        $aid=I('get.aid',0,'intval');
        $cid=intval(cookie('cid'));
        $tid=intval(cookie('tid'));
        $search_word=cookie('search_word');
        $search_word=empty($search_word) ? 0 : $search_word;
        $read=cookie('read');
        // 判断是否已经记录过aid
        if (array_key_exists($aid, $read)) {
            // 判断点击本篇文章的时间是否已经超过一天
            if ($read[$aid]-time()>=86400) {
                $read[$aid]=time();
                // 文章点击量+1
                M('Article')->where(array('aid'=>$aid))->setInc('click',1);
            }
        }else{
            $read[$aid]=time();
            // 文章点击量+1
            M('Article')->where(array('aid'=>$aid))->setInc('click',1);
        }
        cookie('read',$read,864000);
        switch(true){
            case $cid==0 && $tid==0 && $search_word==(string)0:
                $map=array();
                break;
            case $cid!=0:
                $map=array('cid'=>$cid);
                break;
            case $tid!=0:
                $map=array('tid'=>$tid);
                break;
            case $search_word!==0:
                $map=array('title'=>$search_word);
                break;
        }
        // 获取文章数据
        $article=D('Article')->getDataByAid($aid,$map);
        // 如果文章不存在；则返回404页面
        if (empty($article['current']['aid'])) {
            header("HTTP/1.0  404  Not Found");
            $this->display('./Template/default/Home/Public/404.html');
            exit(0);
        }
        // 获取评论数据
        $comment=D('Comment')->getChildData($aid);
        $assign=array(
            'article'=>$article,
            'comment'=>$comment,
            'cid'=>$article['current']['cid']
            );
        if (!empty($_SESSION['user']['id'])) {
            $assign['user_email']=M('Oauth_user')->getFieldById($_SESSION['user']['id'],'email');
        }
        $this->assign($assign);

        $this->display();
    }

    // 随言碎语
    public function chat(){
        $assign=array(
            'data'=>D('Chat')->getDataByState(0,1),
            'cid'=>'chat'
        );
        $this->assign($assign);
        $this->display();
    }

    //笔记库
    public function node(){
        $assign=array(
            'cid'=>'node'
        );
        $this->assign($assign);
        $this->display();
    }

    // 开源项目
    public function git(){


        $cid = I('cid');
        if(empty($cid))$cid = 'all';
        $model = D('ProjectArticle');
        $articles=$model->getProjectPage($cid,1,0,12);
        $count=$model->where(array('is_delete'=>0))->count();
        //$category=M('ProjectCategory')->where(array('pid'=>array('NEQ',0)))->select();
        $sql="select t1.cname,t1.cid,t1.keywords,description,sort,pid,"
            ."(select count(aid) from __PROJECT_ARTICLE__ where cid = t1.cid) as count from __PROJECT_CATEGORY__ as t1 where t1.pid <>0";
        $category = M('ProjectCategory')->query($sql);

        $assign=array(
            'btn_id' => $cid,
            'count'=>$count,
            'procarts'=>$category,
            'projects'=>$articles['data'],
            'page'=>$articles['page'],
            'cid'=>'git'
        );

        $this->assign($assign);
        $this->display();
    }
    //更多项目
    public function more(){
        $categoryModel = M("ProjectCategory");
        $catelist = $categoryModel->order('sort asc')->select();


        $catelist= self::unlimitedForLayer($catelist,$name='child');
        //p($catelist);
        $assign=array(

            'cid'=>'more',
            'procarts'=>$catelist
        );
        $this->assign($assign);

        $this->display();

    }

    //多维数组排序
    static public  function unlimitedForLayer($cate,$name='child',$pid=0)
    {
        $arr=array();
        foreach ($cate as $v) {
            if($v['pid']==$pid)
            {
                $v[$name]=self::unlimitedForLayer($cate,$name,$v['cid']);
                $arr[]=$v;
            }
        }
        return $arr;
    }

    //github项目详情
    public function github(){
        $model = D('ProjectArticle');
        //$category=M('ProjectCategory')->select();
        $sql="select t1.cname,t1.cid,t1.keywords,description,sort,pid,"
            ."(select count(aid) from __PROJECT_ARTICLE__ where cid = t1.cid) as count from __PROJECT_CATEGORY__ as t1 where t1.pid <>0";
        $category = M('ProjectCategory')->query($sql);
        $count=$model->where(array('is_delete'=>0))->count();
        $aid=I('get.aid',0,'intval');
        $cid=I('get.cid',0,'intval');

        $read=cookie('gitread');
        // 判断是否已经记录过aid
        if (array_key_exists($aid, $read)) {
            // 判断点击本篇文章的时间是否已经超过一天
            if ($read[$aid]-time()>=86400) {
                $read[$aid]=time();
                // 文章点击量+1
                $model->where(array('aid'=>$aid))->setInc('click',1);
            }
        }else{
            $read[$aid]=time();
            // 文章点击量+1
            $model->where(array('aid'=>$aid))->setInc('click',1);
        }
        cookie('gitread',$read,864000);
        if($cid != 0) $map=array('cid'=>$cid);
        else $map = array();
        // 获取文章数据
        $data = $model->getDataByAid($aid,$map);
        // 如果文章不存在；则返回404页面
       /* if (empty($article['current']['aid'])) {
            header("HTTP/1.0  404  Not Found");
            $this->display('./Template/default/Home/Public/404.html');
            exit(0);
        }*/

        $assign=array(
            'github'=>$data,
            'count'=>$count,
            'cid'=>'git',
            'procarts'=>$category
        );
        $this->assign($assign);
        $this->display();

    }



    //关于veeker Garden
    public function about(){

        $assign=array(
            'cid'=>'about'
        );
        $this ->assign($assign);
        $this ->display();
    }


    // 站内搜索
    public function search(){
        $search_word=I('get.search_word');
        $articles=D('Article')->getDataByTitle($search_word);
        $assign=array(
            'articles'=>$articles['data'],
            'page'=>$articles['page'],
            'title'=>$search_word,
            'title_word'=>'搜索到的与<span class="b-highlight">'.$search_word.'</span>相关的文章',
            'cid'=>'index'
            );
        $this->assign($assign);
        $this->display('tag');
    }

    // ajax评论文章
    public function ajax_comment(){
        $data=I('post.');
        if(empty($data['content']) || !isset($_SESSION['user']['id'])){
            die('未登录,或内容为空');
        }else{
            $cmtid=D('Comment')->addData(1);
            echo $cmtid;
        }
    }




}
