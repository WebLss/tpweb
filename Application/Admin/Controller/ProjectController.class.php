<?php
/**
 * Created by veeker & Copyright.
 * Date: 2017/10/28 上午10:33
 * Description:项目类包括分类管理和项目管理
 */

namespace Admin\Controller;
use Common\Controller\AdminBaseController;


class ProjectController extends AdminBaseController
{


    // 定义数据表
    private $db;
    private $viewDb;

    // 构造函数 实例化ArticleModel表
    public function __construct(){
        parent::__construct();
        $this->db=D('ProjectArticle');
    }


    //项目列表
    public function index(){
        $data=$this->db->getProjectPage('all');
        //$this->ajaxReturn($data);
        $this->assign('data',$data['data']);
        $this->assign('page',$data['page']);
        $this->display();
    }


    //笔记列表 不显示为笔记
    public function node(){
        $data=$this->db->getNodePage('all');
        //$this->ajaxReturn($data);
        $this->assign('data',$data['data']);
        $this->assign('page',$data['page']);
        $this->display();
    }


    // 添加项目内容
    public function add(){
        if(IS_POST){

            if($aid=$this->db->addData()){
                $baidu_site_url=C('BAIDU_SITE_URL');
                if(!empty($baidu_site_url)){
                    $this->baidu_site($aid);
                }
                $this->success('项目添加成功',U('Admin/Project/index'));
            }else{
                $this->error($this->db->getError());
            }

        }else{
            $allCategory=D('ProjectCategory')->getAllData();
            if(empty($allCategory)){
                $this->error('请先添加分类');
            }

            $this->assign('allCategory',$allCategory);
            $this->display();
        }

    }


    // 修改文章
    public function edit(){
        if(IS_POST){
            if($this->db->editData()){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }else{
            $aid=I('aid');
            $data=$this->db->getDataByAid($aid);
            $allCategory=D('ProjectCategory')->getAllData();
            $this->assign('allCategory',$allCategory);
            $this->assign('data',$data);
            $this->display();
        }
    }
    /**
     * 删除项目
     */
    public function destroy(){



    }




    /**
     * 配合editormd上传图片的方法
     *
     * @return JsonResponse
     */
    public function uploadImage()
    {
        $result = upload('editormd-image-file', 'image/editormd');
        if ($result['status_code'] === 200) {
            $data = [
                'success' => 1,
                'message' => $result['message'],
                'url' => __ROOT__.$result['data']['path'].$result['data']['new_name']
            ];
        } else {
            $data = [
                'success' => 0,
                'message' => $result['message'],
                'url' => ''
            ];
        }
        $this->ajaxReturn($data);
    }

    // 向同步百度推送
    public function baidu_site($aid){
        $urls=array();
        $urls[]=U('Home/Index/git',array('aid'=>$aid),'',true);
        $api=C('BAIDU_SITE_URL');
        $ch=curl_init();
        $options=array(
            CURLOPT_URL=>$api,
            CURLOPT_POST=>true,
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_POSTFIELDS=>implode("\n", $urls),
            CURLOPT_HTTPHEADER=>array('Content-Type: text/plain'),
        );
        curl_setopt_array($ch, $options);
        $result=curl_exec($ch);
        $msg=json_decode($result,true);
        if($msg['code']==500){
            curl_exec($ch);
        }
        curl_close($ch);
    }

}