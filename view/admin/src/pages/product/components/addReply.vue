<template>
  <Modal
    :value="visible"
    :z-index="2"
    title="添加虚拟评论"
    width="700"
    @on-ok="onOk"
    @on-cancel="onCancel"
  >
    <Form :model="formData" :label-width="125">
      <FormItem label="商品">
        <div class="upload-box" @click="callGoods">
          <img v-if="goods.id" :src="goods.image" class="image" />
          <Icon v-else type="ios-add" />
        </div>
      </FormItem>
      <FormItem v-if="goods.id" label="商品规格">
        <div class="upload-box" @click="callAttr">
          <img v-if="attr.unique" :src="attr.image" class="image" />
          <Icon v-else type="ios-add" />
        </div>
      </FormItem>
      <FormItem label="用户头像">
        <div class="upload-box" @click="callPicture('单选')">
          <img v-if="avatar.att_dir" :src="avatar.att_dir" class="image" />
          <Button
              v-if="avatar.att_dir"
              shape="circle"
              icon="md-close"
              class="btn"
              @click.stop="removeUser"
          ></Button>
          <Icon v-else type="ios-add" />
        </div>
      </FormItem>
      <FormItem label="用户名称">
        <Input v-model="formData.nickname" placeholder="请输入用户名称"></Input>
      </FormItem>
      <FormItem label="评价文字">
        <Input
          v-model="formData.comment"
          type="textarea"
          :autosize="{ minRows: 2 }"
          placeholder="请输入评价文字"
        ></Input>
      </FormItem>
      <FormItem label="商品分数">
        <Rate v-model="product_score" />
      </FormItem>
      <FormItem label="服务分数">
        <Rate v-model="service_score" />
      </FormItem>
      <FormItem label="评价图片">
        <div v-for="item in picture" :key="item.att_id" class="upload-box">
          <img :src="item.att_dir" class="image" />
          <Button
            shape="circle"
            icon="md-close"
            class="btn"
            @click="removePicture(item.att_id)"
          ></Button>
        </div>
        <div
          v-if="picture.length < 8"
          class="upload-box"
          @click="callPicture('多选')"
        >
          <Icon type="ios-add" />
        </div>
      </FormItem>
      <FormItem label="评价时间">
        <DatePicker
          :value="add_time"
          type="datetime"
          placeholder="请选择评论时间(不选择默认当前添加时间)"
          style="width: 200px"
          @on-change="onChange"
        />
      </FormItem>
    </Form>
    <template slot="footer">
      <Button @click="onCancel">取消</Button>
      <Button type="primary" @click="onOk">确定</Button>
    </template>
  </Modal>
</template>

<script>
import { saveFictitiousReply } from '@/api/product';
export default {
  props: {
    visible: {
      type: Boolean,
      default: false,
    },
    goods: {
      type: Object,
      default() {
        return {};
      },
    },
    attr: {
      type: Object,
      default() {
        return {};
      },
    },
    avatar: {
      type: Object,
      default() {
        return {};
      },
    },
    picture: {
      type: Array,
      default() {
        return [];
      },
    },
  },
  data() {
    return {
      formData: {
        avatar: '',
        nickname: '',
        comment: '',
      },
      product_score: 0,
      service_score: 0,
      pics: [],
      add_time: '',
    };
  },
  watch: {
    picture(value) {
      this.pics = value.map((item) => {
        return item.att_dir;
      });
    },
    visible(value) {
      if (!value) {
        this.formData.nickname = '';
        this.formData.comment = '';
        this.product_score = 0;
        this.service_score = 0;
        this.add_time = '';
      }
    },
  },
  methods: {
    removeUser(){
      this.avatar.att_dir = '';
    },
    removePicture(att_id) {
      this.$emit('removePicture', att_id);
    },
    onChange(date) {
      this.add_time = date;
    },
    callGoods() {
      this.$emit('callGoods');
    },
    callAttr() {
      this.$emit('callAttr');
    },
    callPicture(type) {
      this.$emit('callPicture', type);
    },
    onOk() {
      if (!this.goods.id) {
        return this.$Message.error('请选择商品');
      }
      if (!this.attr.unique) {
        return this.$Message.error('请选择商品规格');
      }
      if (!this.avatar.att_dir) {
        return this.$Message.error('请选择用户头像');
      }
      if (!this.formData.nickname) {
        return this.$Message.error('请填写用户昵称');
      }
      if (!this.formData.comment) {
        return this.$Message.error('请填写评论内容');
      }
      if (!this.product_score) {
        return this.$Message.error('商品分数必须是1-5之间的整数');
      }
      if (!this.service_score) {
        return this.$Message.error('服务分数必须是1-5之间的整数');
      }
      let data = {
        image: {
          image: this.goods.image,
          product_id: this.goods.id,
        },
        unique: this.attr.unique,
        avatar: this.avatar.att_dir,
        nickname: this.formData.nickname,
        comment: this.formData.comment,
        product_score: this.product_score,
        service_score: this.service_score,
        pics: this.pics,
        add_time: this.add_time,
      };
      saveFictitiousReply(data)
        .then((res) => {
          this.$Message.success(res.msg);
          this.$emit('update:visible', false);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    onCancel() {
      this.$emit('update:visible', false);
    },
  },
};
</script>

<style lang="stylus" scoped>
.upload-box {
  position: relative;
  display: inline-block;
  width: 58px;
  height: 58px;
  border: 1px dashed #c0ccda;
  border-radius: 4px;
  box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
  vertical-align: middle;
  text-align: center;
  line-height: 58px;
  cursor: pointer;

  + .upload-box {
    margin-left: 10px;
  }

  .ivu-icon {
    vertical-align: middle;
    font-size: 20px;
  }

  .image {
    width: 100%;
    height: 100%;
  }

  .btn {
    position: absolute;
    top: 0;
    right: 0;
    width: 20px;
    height: 20px;
    transform: translate(50%, -50%);
  }
}
</style>
