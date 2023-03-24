<template>
  <div>
    <div class="i-layout-page-header">
      <PageHeader class="product_tabs" hidden-breadcrumb>
        <div slot="title">
          <router-link :to="{ path: '/admin/system/crontab' }">
            <div class="font-sm after-line">
              <span class="iconfont iconfanhui"></span>
              <span class="pl10">返回</span>
            </div>
          </router-link>
          <span
            v-text="$route.params.id ? '编辑定时任务' : '添加定时任务'"
            class="mr20 ml16"
          ></span>
        </div>
      </PageHeader>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt form-card">
      <Form
        ref="formValidate"
        :model="formValidate"
        :label-width="97"
        label-colon
      >
        <FormItem label="任务名称" required>
          <Row :gutter="10">
            <Col span="12">
              <Select
                v-model="formValidate.mark"
                label-in-value
                @on-change="taskChange"
              >
                <Option
                  v-for="(value, name) in task"
                  :key="name"
                  :value="name"
                  >{{ value }}</Option
                >
              </Select>
            </Col>
          </Row>
        </FormItem>
        <FormItem label="执行周期" required>
          <Row :gutter="10">
            <Col span="3">
              <Select v-model="formValidate.type">
                <Option
                  v-for="item in typeList"
                  :key="item.value"
                  :value="item.value"
                  >{{ item.name }}</Option
                >
              </Select>
            </Col>
            <Col v-if="formValidate.type == 6" span="3">
              <Select v-model="formValidate.week">
                <Option v-for="item in 7" :key="item" :value="item">{{
                  item | formatWeek
                }}</Option>
              </Select>
            </Col>
            <Col v-if="formValidate.type == 8" span="3">
              <div class="suffix-wrapper">
                <Select v-model="formValidate.month">
                  <Option
                    v-for="(item, index) in 12"
                    :value="item"
                    :key="index"
                    >{{ item }}</Option
                  >
                </Select>
                <span class="suffix">月</span>
              </div>
            </Col>
            <Col
              v-if="
                formValidate.type == 5 ||
                formValidate.type == 7 ||
                formValidate.type == 8
              "
              span="3"
            >
              <div class="suffix-wrapper">
                <Select v-model="formValidate.day">
                  <Option
                    v-for="(item, index) in date"
                    :value="item"
                    :key="index"
                    >{{ item }}</Option
                  >
                </Select>
                <span class="suffix">日</span>
              </div>
            </Col>
            <Col
              v-if="formValidate.type != 1 && formValidate.type != 3"
              span="3"
            >
              <div class="suffix-wrapper">
                <Select v-model="formValidate.hour">
                  <Option
                    v-for="(item, index) in 24"
                    :value="index"
                    :key="index"
                    >{{ index }}</Option
                  >
                </Select>
                <span class="suffix">时</span>
              </div>
            </Col>
            <Col span="3">
              <div class="suffix-wrapper">
                <Select v-model="formValidate.minute">
                  <Option
                    v-for="(item, index) in 60"
                    :value="index"
                    :key="index"
                    >{{ index }}</Option
                  >
                </Select>
                <span class="suffix">分</span>
              </div>
            </Col>
          </Row>
        </FormItem>
        <FormItem label="任务说明">
          <Row :gutter="10">
            <Col span="12">
              <Input
                v-model="formValidate.title"
                type="textarea"
                :autosize="{ minRows: 5, maxRows: 5 }"
                placeholder="请输入任务说明"
              ></Input>
            </Col>
          </Row>
        </FormItem>
        <FormItem label="是否开启">
          <Row :gutter="10">
            <Col span="12">
              <i-switch
                v-model="formValidate.is_open"
                :true-value="1"
                :false-value="0"
                size="large"
              >
                <span slot="open">开启</span>
                <span slot="close">关闭</span>
              </i-switch>
            </Col>
          </Row>
        </FormItem>
      </Form>
    </Card>
    <Card :bordered="false" :padding="14" dis-hover class="btn-card">
      <Button :loading="loading" type="primary" @click="handleSubmit"
        >提交</Button
      >
    </Card>
  </div>
</template>

<script>
import { mapMutations } from 'vuex';
import { timerTask, timerInfo, saveTimer, updateTimer } from '@/api/system';
export default {
  filters: {
    formatWeek(value) {
      return ['周一', '周二', '周三', '周四', '周五', '周六', '周日'][
        value - 1
      ];
    },
  },
  data() {
    return {
      typeList: [
        {
          name: 'N分钟',
          value: 1,
        },
        {
          name: 'N小时',
          value: 2,
        },
        {
          name: 'N天',
          value: 5,
        },
        {
          name: '每小时',
          value: 3,
        },
        {
          name: '每天',
          value: 4,
        },
        {
          name: '每星期',
          value: 6,
        },
        {
          name: '每月',
          value: 7,
        },
        {
          name: '每年',
          value: 8,
        },
      ],
      task: {},
      loading: false,
      formValidate: {
        name: '',
        mark: '', //键
        title: '',
        is_open: 0,
        type: 6,
        month: 1,
        week: 1,
        day: 1,
        hour: 1,
        minute: 30,
        cycle: '',
      },
    };
  },
  computed: {
    date() {
      switch (this.formValidate.month) {
        case 1:
        case 3:
        case 5:
        case 7:
        case 8:
        case 10:
        case 12:
          return 31;
        case 2:
          return 28;
        default:
          return 30;
      }
    },
  },
  watch: {
    date(value) {
      if (value < this.formValidate.day) {
        this.formValidate.day = value;
      }
    },
    'formValidate.type'() {
      this.formValidate.month = 1;
      this.formValidate.week = 1;
      this.formValidate.day = 1;
      this.formValidate.hour = 1;
      this.formValidate.minute = 30;
      this.formValidate.cycle = '';
    },
  },
  created() {
    this.timerTask();
    this.setCopyrightShow({ value: false });
    this.$once('hook:beforeDestroy', () => {
      this.setCopyrightShow({ value: true });
    });
    if (this.$route.params.id) {
      this.timerInfo();
    }
  },
  methods: {
    ...mapMutations('admin/layout', ['setCopyrightShow']),
    timerTask() {
      timerTask().then((res) => {
        this.task = res.data;
      });
    },
    timerInfo() {
      timerInfo(this.$route.params.id).then((res) => {
        let { name, mark, type, cycle, title, is_open } = res.data;
        this.formValidate.name = name;
        this.formValidate.mark = mark;
        this.formValidate.title = title;
        this.formValidate.is_open = is_open;
        this.formValidate.type = type;
        let cycleArr = cycle.split('/');
        this.$nextTick(() => {
          switch (type) {
            case 1:
            case 3:
              this.formValidate.minute = Number(cycleArr[0]);
              break;
            case 2:
            case 4:
              this.formValidate.hour = Number(cycleArr[0]);
              this.formValidate.minute = Number(cycleArr[1]);
              break;
            case 5:
            case 7:
              this.formValidate.day = Number(cycleArr[0]);
              this.formValidate.hour = Number(cycleArr[1]);
              this.formValidate.minute = Number(cycleArr[2]);
              break;
            case 6:
              this.formValidate.week = Number(cycleArr[0]);
              this.formValidate.hour = Number(cycleArr[1]);
              this.formValidate.minute = Number(cycleArr[2]);
              break;
          }
        });
      });
    },
    // 提交
    handleSubmit() {
      // this.loading = true;
      if (!this.formValidate.name) {
        return this.$Message.error({
          content: '请选择任务名称',
          onClose: () => {
            // this.loading = false;
          },
        });
      }
      let data = { ...this.formValidate };
      let cycle = [data.minute];
      switch (data.type) {
        case 2:
        case 4:
          cycle = [data.hour, ...cycle];
          break;
        case 5:
        case 7:
          cycle = [data.day, data.hour, ...cycle];
          break;
        case 6:
          cycle = [data.week, data.hour, ...cycle];
          break;
        case 8:
          cycle = [data.month, data.day, data.hour, ...cycle];
          break;
      }
      data.cycle = cycle.join('/');
      delete data.month;
      delete data.week;
      delete data.day;
      delete data.hour;
      delete data.minute;
      if (this.$route.params.id) {
        this.updateTimer(data);
      } else {
        this.saveTimer(data);
      }
    },
    taskChange(task) {
      let { label, value } = task;
      this.formValidate.name = label;
      this.formValidate.mark = value;
    },
    saveTimer(data) {
      saveTimer(data)
        .then((res) => {
          this.$Message.success({
            content: res.msg,
            onClose: () => {
              this.$router.push({ path: '/admin/system/crontab' });
            },
          });
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    updateTimer(data) {
      updateTimer(this.$route.params.id, data)
        .then((res) => {
          this.$Message.success({
            content: res.msg,
            onClose: () => {
              this.$router.push({ path: '/admin/system/crontab' });
            },
          });
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
  },
};
</script>

<style lang="stylus" scoped>
.form-card {
  margin-bottom: 74px;

  >>> .ivu-card-body {
    padding: 30px 0;
  }
}

.btn-card {
  position: fixed;
  right: 0;
  bottom: 0;
  left: 200px;
  z-index: 2;
  text-align: center;
}

.suffix-wrapper {
  position: relative;
  display: inline-block;
  width: 100%;
  vertical-align: middle;
  line-height: normal;

  .ivu-input-number {
    width: 100%;
    padding-right: 35px;
  }

  >>> .ivu-input-number-handler-wrap {
    right: 35px;
  }

  >>> .ivu-select-arrow {
    right: 35px;
  }

  .suffix {
    position: absolute;
    top: 0;
    right: 0;
    z-index: 1;
    width: 35px;
    height: 100%;
    text-align: center;
    font-size: 12px;
    line-height: 30px;
    color: #333333;
  }
}
</style>