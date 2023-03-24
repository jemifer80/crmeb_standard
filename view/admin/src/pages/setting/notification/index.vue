<template>
<!-- 消息设置 -->
  <div class="message">
    <div class="i-layout-page-header">
      <!-- <PageHeader class="product_tabs" :title="$route.meta.title">
        <div slot="content">
          
        </div>
      </PageHeader> -->
      <div class="table-box">
        <Card :bordered="false" dis-hover class="ivu-mt">
          <div class="new_tab">
            <Tabs v-model="currentTab" @on-click="changeTab">
              <TabPane
                :label="item.label"
                :name="item.value.toString()"
                v-for="(item, index) in headerList"
                :key="index"
              />
            </Tabs>
          </div>
          <Row type="flex" class="mb20" v-if="currentTab==1">
            <Col>
              <Button
                v-auth="['app-wechat-template-sync']"
                type="success"
                @click="syncTemplate"
                class="ml20"
                >同步小程序订阅消息</Button
              >
							<Button
							  v-auth="['app-wechat-wechat-sync']"
							  type="primary"
							  @click="wechatTemplate"
							  class="ml20"
							  >同步公众号模板消息</Button
							>
            </Col>
          </Row>
          <Alert v-if="industry" closable>
            <template slot="desc">
              <div v-if="industry.primary_industry">
                主营行业：{{
                  industry.primary_industry.first_class
                    ? industry.primary_industry.first_class + "||"
                    : industry.primary_industry
                }}
                {{
                  industry.primary_industry.second_class
                    ? industry.primary_industry.second_class
                    : ""
                }}
              </div>
              <div v-if="industry.secondary_industry">
                副营行业：{{
                  industry.secondary_industry.first_class
                    ? industry.primary_industry.first_class + "||"
                    : industry.primary_industry
                }}
                {{
                  industry.primary_industry.second_class
                    ? industry.primary_industry.second_class
                    : ""
                }}
              </div>
              <div class="alert-wrapper">
                <div class="alert-wrapper-head">小程序订阅消息</div>
                <div class="alert-wrapper-body">
                  <div>登录微信小程序后台，基本设置，服务类目增加《生活服务 > 百货/超市/便利店》 <span>(否则同步小程序订阅消息会报错)</span></div>
                  <div>同步小程序订阅消息 是在小程序后台未添加订阅消息模板的前提下使用的，会新增一个模板消息并把信息同步过来，如果小程序后台已经添加过的，会跳过不会更新本项目数据库。</div>
                </div>
              </div>
              <div class="alert-wrapper">
                <div class="alert-wrapper-head">微信模板消息</div>
                <div class="alert-wrapper-body">
                  <div>登录微信公众号后台，选择模板消息，将模板消息的所在行业修改副行业为《其他/其他》 <span>(否则同步模板消息不成功)</span></div>
                  <div>同步公众号模板消息 同步公众号模板会删除公众号后台现有的模板，并重新添加新的模板，然后同步信息到数据库，如果多个项目使用同一个公众号的模板，请谨慎操作。</div>
                </div>
              </div>
            </template>
          </Alert>
          <Table
            :columns="columns"
            :data="levelLists"
            ref="table"
            class="mt25"
            :loading="loading"
            highlight-row
            no-userFrom-text="暂无数据"
            no-filtered-userFrom-text="暂无筛选结果"
          >
            <template slot-scope="{ row, index }" slot="name">
              <span class="table">
                {{ row.name }}
              </span>
            </template>
            <template slot-scope="{ row, index }" slot="title">
              <span class="table">{{ row.title }}</span>
            </template>
            <template
              slot-scope="{ row, index }"
              v-for="(item, index) in [
                'is_system',
                'is_wechat',
                'is_routine',
                'is_sms',
                'is_ent_wechat',
              ]"
              :slot="item"
            >
              <i-switch
                v-model="row[item]"
                :value="row[item]"
                :true-value="1"
                :false-value="2"
                @on-change="changeSwitch(row, item)"
                size="large"
                v-if="row[item] > 0"
              >
                <span slot="open">开启</span>
                <span slot="close">关闭</span>
              </i-switch>
            </template>
            <template slot-scope="{ row, index }" slot="setting">
              <span class="setting btn" @click="setting(item, row)">设置</span>
            </template>
          </Table>
        </Card>
      </div>
    </div>
  </div>
</template>

<script>
import {
  getNotificationList,
  getNotificationInfo,
  noticeStatus,
} from "@/api/notification.js";
import { routineSyncTemplate, wechatSyncTemplate } from "@/api/app";
export default {
  data() {
    return {
      modalTitle: "",
      notificationModal: false,
      headerList: [
        { label: "通知会员", value: "1" },
        { label: "通知平台", value: "2" },
      ],
      columns: [
        {
          title: "ID",
          key: "id",
          align: "center",
          width: 50,
        },
        {
          title: "通知类型",
          slot: "name",
          align: "center",
          width: 200,
        },
        {
          title: "通知场景说明",
          slot: "title",
          align: "center",
          minWidth: 200,
        },
        {
          title: "站内信",
          slot: "is_system",
          align: "center",
          minWidth: 100,
        },
        {
          title: "公众号模板",
          slot: "is_wechat",
          align: "center",
          minWidth: 100,
        },
        {
          title: "小程序订阅",
          slot: "is_routine",
          align: "center",
          minWidth: 100,
        },
        {
          title: "发送短信",
          slot: "is_sms",
          align: "center",
          minWidth: 100,
        },
        {
          title: "企业微信",
          slot: "is_ent_wechat",
          align: "center",
          minWidth: 100,
        },
        {
          title: "设置",
          slot: "setting",
          width: 150,
          align: "center",
        },
      ],
      levelLists: [],
      currentTab: "1",
      loading: false,
      formData: {},
      industry: null,
    };
  },
  created() {
    this.changeTab(this.currentTab);
  },
  methods: {
    changeSwitch(row, item) {
      noticeStatus(item, row[item], row.id)
        .then((res) => {
          this.$Message.success(res.msg);
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    changeTab(data) {
      getNotificationList(data).then((res) => {
        this.levelLists = res.data.list;
        this.industry = res.data.industry;
      });
    },
    // 同步订阅消息
    syncTemplate() {
      routineSyncTemplate()
        .then((res) => {
          this.$Message.success(res.msg);
          this.changeTab(this.currentTab);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
		// 同步公众号模板消息
		wechatTemplate() {
		  wechatSyncTemplate()
		    .then((res) => {
		      this.$Message.success(res.msg);
		      this.changeTab(this.currentTab);
		    })
		    .catch((res) => {
		      this.$Message.error(res.msg);
		    });
		},
    // 开启关闭
    changeStatus() {},
    // 列表
    notice() {},
    // 设置
    setting(item, row) {
      this.$router.push({
        path: "/admin/setting/notification/notificationEdit?id=" + row.id,
      });
    },
    getData(keys, row, item) {
      this.formData = {};
      getNotificationInfo(row.id, item).then((res) => {
        keys.map((i, v) => {
          this.formData[i] = res.data[i];
        });
        this.formData.type = item;
        this.notificationModal = true;
      });
    },
  },
};
</script>

<style scoped lang="stylus">
.message /deep/ .ivu-table-header table {
  /* border-top: 1px solid #e8eaec !important;
  border-left: 1px solid #e8eaec !important; */
}
.message /deep/ .ivu-table-header thead tr th {
  padding: 8px 16px;
}
.message /deep/ .ivu-tabs-tab {
  border-radius: 0 !important;
}
/* .table-box {
  padding: 20px;
} */
.is-table {
  display: flex;
  /* justify-content: space-around; */
  justify-content: center;
}
.btn {
  padding: 6px 12px;
  cursor: pointer;
  color: #2d8cf0;
  font-size: 12px;
  border-radius: 3px;
}
.is-switch-close {
  background-color: #504444;
}
.is-switch {
  background-color: #eb5252;
}
.notice-list {
  background-color: #308cf5;
  margin: 0 15px;
}
.table {
  padding: 0 18px;
}
.new_tab {
		>>>.ivu-tabs-nav .ivu-tabs-tab{
			padding:4px 16px 20px !important;
			font-weight: 500;
		}
	}
  .alert-wrapper {
    font-size: 12px;
    color: #495060;
    margin-top: 5px;

    +.alert-wrapper {
      margin-top: 15px;
    }

    &-head {
      margin-bottom: 5px
      font-weight: bold;
    }

    &-body {
      span {
        color: #ff9400;
      }
    }
  }
</style>
