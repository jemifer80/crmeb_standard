<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt" :padding="0">
      <div class="new_card_pd">
        <Form
          ref="formValidate"
          :model="formValidate"
          inline
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.native.prevent
        >
          <FormItem label="状态：">
            <Select
              v-model="formValidate.status"
              placeholder="请选择"
              clearable
              class="input-add"
            >
              <Option value="1">开启</Option>
              <Option value="2">禁用</Option>
            </Select>
          </FormItem>
          <FormItem label="搜索：">
            <Input
              v-model="formValidate.name"
              placeholder="请输入账号或者描述"
              class="input-add mr14"
            />
            <Button type="primary" @click="userSearchs">查询</Button>
          </FormItem>
        </Form>
      </div>
    </Card>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Button type="primary" @click="add">添加账号</Button>
      <Table
        :columns="columns1"
        :data="levelLists"
        ref="table"
        class="mt25"
        :loading="loading"
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row }" slot="status">
          <i-switch
            v-model="row.status"
            :value="row.status"
            :true-value="1"
            :false-value="2"
            @on-change="onchangeIsShow(row)"
            size="large"
          >
            <span slot="open">开启</span>
            <span slot="close">禁用</span>
          </i-switch>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="setUp(row)">设置</a>
          <Divider type="vertical" />
          <a @click="edit(row)">编辑</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除该账户', index)">删除</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="formValidate.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="formValidate.limit"
        />
      </div>
    </Card>
    <Modal
      v-model="modals"
      scrollable
      :title="type == 0 ? '添加账号' : '编辑账号'"
      :mask-closable="false"
      :closable="false"
    >
      <Form
        ref="modalsdate"
        :model="modalsdate"
        :rules="ruleValidate"
        :label-width="60"
        label-position="right"
      >
        <FormItem label="账号" prop="appid">
          <div style="display: flex">
            <Input
              type="text"
              v-model="modalsdate.appid"
              :disabled="type != 0"
            ></Input>
          </div>
        </FormItem>
        <FormItem label="密码" :prop="modalsid ? 'appsecret' : ''">
          <div style="display: flex">
            <Input
              type="text"
              v-model="modalsdate.appsecret"
              class="input"
            ></Input>
            <Button type="primary" @click="reset" class="reset">重置</Button>
          </div>
        </FormItem>
        <FormItem label="描述">
          <div style="display: flex">
            <Input type="textarea" v-model="modalsdate.title"></Input>
          </div>
        </FormItem>
        <FormItem label="接口权限" prop="title">
          <Tree :data="intList" multiple show-checkbox ref="tree"></Tree>
        </FormItem>
      </Form>
      <div slot="footer">
        <Button v-if="modalsid == ''" type="primary" @click="ok('modalsdate')"
          >确定</Button
        >
        <Button v-else type="primary" @click="oks('modalsdate')">确定</Button>
        <Button @click="cancel">取消</Button>
      </div>
    </Modal>
    <Modal
      v-model="settingModals"
      scrollable
      title="设置推送"
      :mask-closable="false"
      width="900"
      :closable="false"
    >
      <Form
        class="setting-style"
        ref="settingData"
        :model="settingData"
        :rules="type == 0 ? ruleValidate : editValidate"
        :label-width="140"
        label-position="right"
      >
        <FormItem label="推送开关" prop="switch">
          <Switch
            v-model="settingData.push_open"
            :true-value="1"
            :false-value="0"
          />
        </FormItem>
        <FormItem label="推送账号" prop="push_account">
          <div class="form-content">
            <Input
              type="text"
              v-model="settingData.push_account"
              placeholder="请输入推送账号"
            ></Input>
            <span class="trip">接受推送方获取token的账号</span>
          </div>
        </FormItem>
        <FormItem label="推送密码" prop="push_password">
          <div class="form-content">
            <Input
              type="text"
              v-model="settingData.push_password"
              placeholder="请输入推送密码"
            ></Input>
            <span class="trip">接受推送方获取token的密码</span>
          </div>
        </FormItem>
        <FormItem label="获取TOKEN接口" prop="push_token_url">
          <div class="form-content">
            <div class="input-button">
              <Input
                type="text"
                v-model="settingData.push_token_url"
                placeholder="请输入获取TOKEN接口"
              ></Input>
              <Button
                class="ml10"
                type="primary"
                @click="textOutUrl(settingData.id)"
                >测试链接</Button
              >
            </div>
            <span class="trip"
              >接受推送方获取token的URL地址，POST方法，传入push_account和push_password，返回token和有效时间time(秒)</span
            >
          </div>
        </FormItem>
        <FormItem label="用户数据修改推送接口" prop="user_update_push">
          <div class="form-content">
            <Input
              type="text"
              v-model="settingData.user_update_push"
              placeholder="请输入用户数据修改推送接口"
            ></Input>
            <span class="trip"
              >用户修改积分，余额，经验等将用户信息推送至该地址，POST方法</span
            >
          </div>
        </FormItem>
        <FormItem label="订单创建推送接口" prop="order_create_push">
          <div class="form-content">
            <Input
              type="text"
              v-model="settingData.order_create_push"
              placeholder="请输入订单创建推送接口"
            ></Input>
            <span class="trip">订单创建时推送订单信息至该地址，POST方法</span>
          </div>
        </FormItem>
        <FormItem label="订单支付推送接口" prop="order_pay_push">
          <div class="form-content">
            <Input
              type="text"
              v-model="settingData.order_pay_push"
              placeholder="请输入订单支付推送接口"
            ></Input>
            <span class="trip"
              >订单完成支付时推送订单已支付信息至该地址，POST方法</span
            >
          </div>
        </FormItem>
        <FormItem label="售后订单创建推送接口" prop="refund_create_push">
          <div class="form-content">
            <Input
              type="text"
              v-model="settingData.refund_create_push"
              placeholder="请输入售后订单创建推送接口"
            ></Input>
            <span class="trip"
              >售后订单生成时推送售后单信息至该地址，POST方法</span
            >
          </div>
        </FormItem>
        <FormItem label="售后订单取消推送接口" prop="refund_cancel_push">
          <div class="form-content">
            <Input
              type="text"
              v-model="settingData.refund_cancel_push"
              placeholder="请输入售后订单取消推送接口"
            ></Input>
            <span class="trip"
              >售后订单取消时推送售后单取消信息至该地址，POST方法</span
            >
          </div>
        </FormItem>
      </Form>
      <div slot="footer">
        <Button type="primary" @click="submit('settingData')">确定</Button>
        <Button @click="settingModals = false">取消</Button>
      </div>
    </Modal>
  </div>
</template>
<script>
import { mapState } from "vuex";
import {
  outListApi,
  outStatusApi,
  outSaveApi,
  outSavesApi,
  interfaceList,
  setUpPush,
  textOutUrl,
} from "@/api/setting";
export default {
  name: "user_level",
  data() {
    return {
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      modalsid: "",
      loading: false,
      columns1: [
        {
          title: "ID",
          key: "id",
          width: 50,
        },
        {
          title: "账号",
          key: "appid",
          minWidth: 100,
        },
        {
          title: "描述",
          key: "title",
          minWidth: 120,
        },
        {
          title: "添加时间",
          key: "add_time",
          minWidth: 100,
        },
        {
          title: "最后登录时间",
          key: "last_time",
          minWidth: 100,
        },
        {
          title: "登录",
          key: "ip",
          minWidth: 100,
        },
        {
          title: "状态",
          slot: "status",
          minWidth: 120,
        },
        {
          title: "操作",
          slot: "action",
          fixed: "right",
          minWidth: 80,
        },
      ],
      formValidate: {
        name: "",
        status: "",
        page: 1,
        limit: 15,
      },
      modalsdate: {
        appid: "",
        appsecret: "",
        title: "",
        rules: [],
      },
      ruleValidate: {
        appid: [
          {
            required: true,
            message: "请输入正确的账号 (不少于8位数)",
            trigger: "blur",
            min: 8,
          },
        ],
        appsecret: [
          {
            required: true,
            message: "请输入正确的密码 (不少于32位数)",
            trigger: "blur",
            min: 32,
          },
        ],
      },
      levelLists: [],
      total: 0,
      modals: false,
      type: 0,
      intList: [],
      settingModals: false,
      settingData: {
        switch: 1,
        name: "",
      },
    };
  },
  created() {
    this.getList();
  },
  computed: {
    ...mapState("admin/layout", ["isMobile"]),
    labelWidth() {
      return this.isMobile ? undefined : 96;
    },
    labelPosition() {
      return this.isMobile ? "top" : "right";
    },
  },
  methods: {
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `setting/system_out/delete/${row.id}`,
        method: "DELETE",
        ids: "",
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.levelLists.splice(num, 1);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 修改是否显示
    onchangeIsShow(row) {
      outStatusApi(row.id, row.status)
        .then((res) => {
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 等级列表
    getList() {
      this.loading = true;
      outListApi(this.formValidate)
        .then((res) => {
          let data = res.data;
          this.levelLists = data.list;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.formValidate.page = index;
      this.getList();
    },
    // 添加
    add() {
      this.modals = true;
      this.type = 0;
      this.modalsdate = {
        appid: "",
        appsecret: "",
        title: "",
        rules: [],
      };
      this.getIntList();
    },
    // 编辑
    edit(row) {
      console.log(row);
      this.modals = true;
      this.modalsdate.appid = row.appid;
      this.modalsdate.title = row.title;
      this.modalsdate.rules = row.rules.map((e) => {
        return Number(e);
      });

      this.modalsid = row.id;
      console.log(this.modalsid);
      this.type = 1;
      this.getIntList("edit", this.modalsdate.rules);
    },
    getIntList(type, list) {
      interfaceList().then((res) => {
        this.intList = res.data;
        if (!type) {
          this.intList.map((item) => {
            if (item.id === 1) {
              item.checked = true;
              item.disableCheckbox = true;
              if (item.children.length) {
                item.children.map((v) => {
                  v.checked = true;
                  v.disableCheckbox = true;
                });
              }
            }
          });
        } else {
          list.map((item) => {
            this.intList.map((e) => {
              if (e.id === 1) {
                e.checked = true;
                e.disableCheckbox = true;
                if (e.children.length) {
                  e.children.map((v) => {
                    v.checked = true;
                    v.disableCheckbox = true;
                  });
                }
              }
              listData(e.children || [], item);
            });
          });
        }
        function listData(list, id) {
          if (list.length) {
            list.map((v) => {
              if (v.id == id) {
                v.checked = true;
              }
              if (v.children) {
                listData(v.children);
              }
            });
          }
        }
      });
    },
    ok(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.modalsdate.rules = [];
          this.$refs.tree.getCheckedAndIndeterminateNodes().map((node) => {
            this.modalsdate.rules.push(node.id);
          });
          outSaveApi(this.modalsdate)
            .then((res) => {
              this.modalsdate = {
                appid: "",
                appsecret: "",
                title: "",
                rules: [],
              };
              (this.modals = false), this.$Message.success(res.msg);
              this.modalsid = "";
              this.getList();
            })
            .catch((err) => {
              this.$Message.error(err.msg);
            });
        } else {
          this.$Message.warning("请完善数据");
        }
      });
    },
    oks(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.modalsdate.rules = [];
          this.$refs.tree.getCheckedAndIndeterminateNodes().map((node) => {
            this.modalsdate.rules.push(node.id);
          });
          outSavesApi(this.modalsid, this.modalsdate)
            .then((res) => {
              this.modalsdate = {
                appid: "",
                appsecret: "",
                title: "",
                rules: [],
              };
              (this.modals = false), this.$Message.success(res.msg);
              this.modalsid = "";
              this.getList();
            })
            .catch((err) => {
              this.$Message.error(err.msg);
            });
        } else {
          this.$Message.warning("请完善数据");
        }
      });
    },
    cancel() {
      this.modalsid = "";
      this.modalsdate = {
        appid: "",
        appsecret: "",
        title: "",
        rules: [],
      };
      this.modals = false;
    },
    setUp(row) {
      this.settingModals = true;
      this.settingData = row;
    },
    submit(name) {
      setUpPush(this.settingData).then((res) => {
        this.$Message.success(res.msg);
        this.settingModals = false;
        this.getList();
      });
    },
    textOutUrl() {
      textOutUrl(this.settingData)
        .then((res) => {
          this.$Message.success(res.msg);
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    reset() {
      let len = 32;
      let chars = "ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678";
      let maxPos = chars.length;
      let pwd = "";
      for (let i = 0; i < len; i++) {
        pwd += chars.charAt(Math.floor(Math.random() * maxPos));
      }
      this.modalsdate.appsecret = pwd;
    },
    // 表格搜索
    userSearchs() {
      this.formValidate.page = 1;
      this.getList();
    },
  },
};
</script>

<style scoped lang="stylus">
.reset {
  margin-left: 10px;
}

.input-button {
  display: flex;
}
</style>
