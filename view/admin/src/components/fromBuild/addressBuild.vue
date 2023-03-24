<template>
  <div>
    <FormItem :label="title" class="input-build" :class="getClassName()">
      <div class="input-error-wrapper">
        <Cascader
          :data="addresData"
          :load-data="loadData"
          v-model="valueData"
          @on-change="addchack"
          class="inputW"
        />
        <!--错误提醒-->
        <div v-if="errorMessage && !copy" class="error-wrapper">
          {{ errorMessage }}
        </div>
      </div>
      <!--说明-->
      <div v-if="info" class="info-wrapper">{{ info }}</div>
    </FormItem>
  </div>
</template>
<script>
import { cityApi } from '@/api/store'
import build from './build'

export default {
  name: '',
  mixins: [build],
  components: {},
  props: {},
  data() {
    return {
      addresData: [],
      address: '',
      // valueModel:[]
      valueData:[]
    }
  },
  computed: {},
  watch: {},
  created() {
    let data = { pid: 0 }
    this.cityInfo(data)
    this.valueModel.map(item=>{
      this.valueData.push(item.id)

    })
  },
  mounted() {},
  methods: {
    cityInfo(data) {
      cityApi(data).then((res) => {
        this.addresData = res.data
      })
    },
    addchack(e, selectedData) {
      this.address = selectedData.map((o) => o.label).join('/')
      this.$emit('changeValue', { field: this.field, value: selectedData })
    },
    loadData(item, callback) {
      item.loading = true
      cityApi({ pid: item.value }).then((res) => {
        item.children = res.data
        item.loading = false
        callback()
      })
    },
  },
}
</script>
<style scoped lang="stylus">
.mapBox >>> .ivu-modal-body {
  height: 640px !important;
}
</style>