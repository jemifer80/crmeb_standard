<template>
  <div>
    <div class="mt20 ml20">
      <Form>
        <FormItem label="视频类型：">
		      <RadioGroup v-model="seletVideo" @on-change="changeVideo">
		        <Radio :label="0" class="radio">本地视频</Radio>
		        <Radio :label="1">视频链接</Radio>
		      </RadioGroup>
		    </FormItem>
        <FormItem label="视频链接"  v-if="seletVideo == 1">
          <Input class="input-add mr14" v-model="videoLink" placeholder="请输入视频链接" />
          <Button type="primary" @click="zh_uploadFile">确认添加</Button>
        </FormItem>
        <FormItem label="本地上传：" v-if="seletVideo == 0">
         <input
          type="file"
          ref="refid"
          class="display-add"
          @change="zh_uploadFile_change"
        />
        <Button
          v-if="upload_type !== '1' || videoLink"
          type="primary"
          icon="ios-cloud-upload-outline"
          class="ml10"
          @click="zh_uploadFile"
          >上传视频</Button
        >
      <Upload
        v-if="upload_type === '1' && !videoLink"
        :show-upload-list="false"
        :action="fileUrl2"
        class="ml10"
        :before-upload="beforeUpload"
        :data="uploadData"
        :headers="header"
        :multiple="true"
        :on-success="handleSuccess"
        style="display: inline-block"
      >
        <Button type="primary" icon="ios-cloud-upload-outline">上传视频</Button>
      </Upload>
      <Progress :percent="progress" :stroke-width="5" v-if="upload.videoIng" />
        </FormItem>
      </Form>
      <div class="iview-video-style" v-if="formValidate.video_link">
        <video
          class="video-style"
          :src="formValidate.video_link"
          controls="controls"
        >
          您的浏览器不支持 video 标签。
        </video>
        <div class="mark"></div>
        <Icon type="ios-trash-outline" class="iconv" @click="delVideo" />
      </div>
    </div>
    <div class="mt50 ml20">
      <Button type="primary" @click="uploads">确认</Button>
    </div>
  </div>
</template>

<script>
import { productGetTempKeysApi, uploadType, videoAttachment } from "@/api/product";
import { uploadByPieces } from "@/utils/upload"; //引入uploadByPieces方法
import Setting from "@/setting";
import util from "@/libs/util";
// import "../../../public/UEditor/dialogs/internal";
export default {
  name: "vide11o",
  props:{
      pid:{
        type: Number,
        default: 0
      }
  },
  data() {
    return {
      fileUrl: Setting.apiBaseURL + "/file/upload",
      fileUrl2: Setting.apiBaseURL + "/file/video_upload",
      upload: {
        videoIng: false, // 是否显示进度条；
      },
      progress: 0, // 进度条默认0
      videoLink: "",
      formValidate: {
        video_link: "",
      },
      upload_type: "",
      uploadData: {},
      header: {},
      seletVideo:0,
    };
  },
  watch: {
    seletVideo: {
        handler (nVal, oVal) {
            this.videoLink=''
            this.formValidate.video_link = ''
        },
    }
},
  created() {
    this.uploadType();
    this.getToken();
  },
  methods: {
    // 删除视频；
    delVideo() {
      let that = this;
      that.$set(that.formValidate, "video_link", "");
      that.$refs.refid.value = '';
    },
    // 上传成功
    handleSuccess(res, file, fileList) {
      if (res.status === 200) {
        this.formValidate.video_link = res.data.src;
        this.$Message.success(res.msg);
      } else {
        this.$Message.error(res.msg);
      }
    },
	getToken() {
	  this.header["Authori-zation"] = "Bearer " + util.cookies.get("token");
	},
    //获取视频上传类型
    uploadType() {
      uploadType().then((res) => {
        //1:本地上传；2：七牛云上传；3：阿里云上传；4：腾讯云上传
        this.upload_type = res.data.upload_type;
      });
    },
    beforeUpload(file) {
		let imgTypeArr = ["video/mp4"];
		let imgType = imgTypeArr.indexOf(file.type) !== -1
		if (!imgType) {
			return this.$Message.warning({
			content:  '文件  ' + file.name + '  格式不正确, 请选择格式正确的视频',
			duration: 5
			});
		}
    this.upload.videoIng = true;
    this.progress = 20;
	  uploadByPieces({
	    randoms: "", // 随机数，这里作为给后端处理分片的标识 根据项目看情况 是否要加
	    file: file, // 视频实体
	    pieceSize: 3, // 分片大小
        pid:this.pid,
	    success: (data) => {
        let that = this;
	      this.formValidate.video_link = data.file_path;
	      this.progress = 100;
        setTimeout(function(){
          that.upload.videoIng = false;
        },100)
	    },
	    error: (e) => {
	      this.$Message.error(e.msg);
        this.upload.videoIng = false;
	    },
	    uploading: (chunk, allChunk) => {
	      let st = Math.floor((chunk / allChunk) * 100);
	      this.progress = st;
	    },
	  });
	  return false;
	},
    zh_uploadFile() {
      if (this.videoLink && this.$getFileType(this.videoLink) == 'video') {
        this.formValidate.video_link = this.videoLink;
      } else if(this.videoLink && this.$getFileType(this.videoLink) !== 'video' && this.seletVideo == 1){
        return this.$Message.error("请输入正确的视频链接")
      } else{
        this.$refs.refid.click();
      }
    },
    zh_uploadFile_change(evfile) {
      let that = this;
      if (evfile.target.files[0].type !== "video/mp4") {
        return that.$Message.error("只能上传mp4文件");
      }
      productGetTempKeysApi()
              .then((res) => {
                that.$videoCloud
                        .videoUpload({
                          type: res.data.type,
                          evfile: evfile,
                          res: res,
                          uploading(status, progress) {
                            that.upload.videoIng = status;
                            if (res.status == 200) {
                              that.progress = 100;
                            }
                          },
                        })
                        .then((res) => {
                          that.formValidate.video_link = res.url;
                          that.$Message.success("视频上传成功");
                          that.upload.videoIng = false;
                        })
                        .catch((res) => {
                          that.$Message.error(res);
                        });
              })
              .catch((res) => {
                that.$Message.error(res.msg);
              });
    },
    uploads() {
      if(this.seletVideo == 0 && this.formValidate.video_link) {

      }else if(this.seletVideo == 0 && this.formValidate.video_link == '') {
        this.$Message.error("请上传视频");
        nowEditor.dialog.close(true);
      }
      if(this.seletVideo == 1 && this.videoLink == ''){
        this.$Message.error("请上传视频");
        nowEditor.dialog.close(true);
      }
      if(this.videoLink && this.$getFileType(this.videoLink) !== 'video' && this.seletVideo == 1){
        this.$Message.error("请输入正确的视频链接")
      } else {
        if (this.seletVideo == 1 || (this.seletVideo == 0 && this.upload_type != 1)){
          videoAttachment({
            path:this.formValidate.video_link,
            pid:this.pid,
            upload_type: this.seletVideo == 1 ? 1 : this.upload_type
          }).then(res=>{
            this.$emit('getVideo',this.formValidate.video_link);
            this.formValidate.video_link = this.videoLink = '';
            if (this.$refs.refid) {
              this.$refs.refid.value = "";
            }
          });
        } else {
          this.$emit('getVideo',this.formValidate.video_link);
          this.formValidate.video_link = this.videoLink = '';
          if (this.$refs.refid) {
            this.$refs.refid.value = "";
          }
        }
      }
    },
    changeVideo(e){
      this.videoLink = "";
    }
  },
};
</script>

<style scoped>
.video-style {
width: 100%;
height: 100% !important;
border-radius: 10px
}
.iview-video-style {
  width: 40%;
  height: 180px;
  border-radius: 10px;
  background-color: #707070;
  margin-top: 10px;
  position: relative;
  overflow: hidden;
}
.iview-video-style .iconv {
  color: #fff;
  line-height: 180px;
  width: 50px;
  height: 50px;
  display: inherit;
  font-size: 26px;
  position: absolute;
  top: -74px;
  left: 50%;
  margin-left: -25px;
}
.iview-video-style .mark {
  position: absolute;
  width: 100%;
  height: 30px;
  top: 0;
  background-color: rgba(0, 0, 0, 0.5);
  text-align: center;
}
</style>
