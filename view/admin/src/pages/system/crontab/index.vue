<template>
  <Card :bordered="false" dis-hover>
    <Button
      v-auth="['system-crontab-create']"
      type="primary"
      to="/admin/system/crontab/create"
      >添加定时任务</Button
    >
    <Table
      :columns="columns"
      :data="tableData"
      :loading="loading"
      class="ivu-mt"
    >
      <template slot-scope="{ row }" slot="is_open">
        <i-switch
          v-model="row.is_open"
          :true-value="1"
          :false-value="0"
          size="large"
          @on-change="handleChange(row)"
        >
          <span slot="open">开启</span>
          <span slot="close">关闭</span>
        </i-switch>
      </template>
      <template slot-scope="{ row }" slot="action">
        <router-link :to="`/admin/system/crontab/create/${row.id}`"
          >编辑</router-link
        >
        <Divider type="vertical" />
        <a @click="handleDelete(row, '删除定时任务', index)">删除</a>
      </template>
    </Table>
    <div class="acea-row row-right page">
      <Page
        :total="total"
        :current="page"
        show-elevator
        show-total
        @on-change="pageChange"
        :page-size="limit"
      />
    </div>
  </Card>
</template>

<script>
import { timerIndex, showTimer } from '@/api/system';

export default {
  name: 'system_crontab',
  data() {
    return {
      loading: false,
      columns: [
        {
          title: '名称',
          key: 'name',
          minWidth: 150,
        },
        {
          title: '最后执行时间',
          key: 'last_execution_time',
          minWidth: 150,
        },
        {
          title: '执行周期',
          key: 'execution_cycle',
          minWidth: 150,
        },
        {
          title: '是否开启',
          slot: 'is_open',
          minWidth: 100,
        },
        {
          title: '操作',
          slot: 'action',
          align: 'center',
          fixed: 'right',
          minWidth: 100,
        },
      ],
      tableData: [],
      page: 1,
      limit: 15,
      total: 0,
    };
  },
  created() {
    this.getList();
  },
  methods: {
    // 列表
    getList() {
      this.loading = true;
      timerIndex({
        page: this.page,
        limit: this.limit,
      })
        .then((res) => {
          this.loading = false;
          let { count, list } = res.data;
          this.total = count;
          this.tableData = list;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    // 删除
    handleDelete(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `system/timer/del/${row.id}`,
        method: 'get',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 是否开启
    handleChange({ id, is_open }) {
      showTimer(id, is_open)
        .then((res) => {
          this.$Message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.page = index;
      this.getList();
    },
  },
};
</script>

<style lang="stylus" scoped></style>