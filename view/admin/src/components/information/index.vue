<template>
  <div class="information">
    <Modal
      v-model="isShow"
      title="选择信息"
      footerHide
      class="paymentFooter"
      scrollable
      width="900"
      @on-cancel="cancel"
    >
      <Form ref="formValidate" :model="formValidate" :label-width="100">
        <FormItem label="信息搜索：">
          <Input
            v-model="formValidate.info"
            placeholder="请输入信息"
            class="inputw"
          />
          <Button type="primary" @click="handleSubmit">
            查询
          </Button>
        </FormItem>
      </Form>
      <Table
        ref="table"
        no-data-text="暂无数据"
        no-filtered-data-text="暂无筛选结果"
        :columns="columns1"
        :data="listOneNew"
        :loading="loading"
        class="mr-20"
        @on-selection-change="selectionTap"
      ></Table>
      <div class="footer mt20">
        <Button class="btn" @click="cancel">取消</Button>
        <Button type="primary" class="btn" @click="ok">确认</Button>
      </div>
    </Modal>
  </div>
</template>
<script>
export default {
  name: '',
  components: {},
  props: {
    listOne: {
      type: Array,
      default: [],
    },
  },
  data() {
    return {
      formValidate: {
        info: '',
      },
      isShow: false,
      loading: false,
      //选中信息集合
      selectEquips: [],
      columns1: [
        {
          type: 'selection',
          width: 60,
          align: 'center',
        },
        {
          title: '信息',
          key: 'info',
        },
        {
          title: '信息格式',
          key: 'label',
        },
        {
          title: '提示信息',
          key: 'tip',
        },
      ],
      listOneNew:[]
    }
  },
  computed: {},
  watch: {
    listOne:{
      handler: function(n) {
        this.listOneNew = n;
      }
    }
  },
  created() {},
  mounted() {},
  methods: {
    handleSubmit(){
      if(this.formValidate.info){
        let obj = [];
        this.listOne.forEach(item=>{
          if(item.info.indexOf(this.formValidate.info) !=-1){
            obj.push(item)
          }
        });
        this.$set(this,'listOneNew',obj);
      }else{
        this.$set(this,'listOneNew',this.listOne);
      }
    },
    selectionTap(data){
      this.selectEquips = data;
    },
    ok() {
      this.$emit('getInfoList', this.selectEquips)
      this.reset();
    },
    cancel() {
      this.isShow = false;
      this.reset();
    },
    reset(){
      this.formValidate.info = '';
      this.$refs.table.selectAll(false);
      this.listOneNew = this.listOne
    }
  },
}
</script>
<style scoped lang="stylus">
.inputw {
 width: 250px;
 margin-right: 14px;
}
.footer {
display: flex;
justify-content: right ;
.btn {
margin-right 14px
width: 54px;
height:32px;
}
}
</style>
