<template>
  <div>
    <FormItem :label="title" class="input-build" :class="getClassName()">
      <div class="input-error-wrapper">
        <Input
          search
          enter-button="查找位置"
          v-model="valueModel"
          :placeholder="placeholder"
          class="inputW"
          @on-search="onSearch(valueModel)"
        />
        <!--错误提醒-->
        <div v-if="errorMessage && !copy" class="error-wrapper">
          {{ errorMessage }}
        </div>
      </div>
      <!--说明-->
      <div v-if="info" class="info-wrapper">{{ info }}</div>
    </FormItem>

    <Col span="16">
      <Modal
        v-model="modalMap"
        scrollable
        footer-hide
        closable
        :mask-closable="false"
        :z-index="1"
        class="mapBox"
        title="请选择地址"
      >
        <iframe
          id="mapPage"
          width="100%"
          height="550px"
          frameborder="0"
          v-bind:src="keyUrl"
        ></iframe>
      </Modal>
    </Col>
  </div>
</template>
<script>
import Maps from '@/components/map/map.vue'

import build from './build'
import { keyApi } from '@/api/store'
export default {
  name: '',
  mixins: [build],
  components: {
    Maps,
  },
  data() {
    return {
      modalMap: false,
      latitude: '',
      longitude: '',
      showMap: false,
      keyUrl: '',
    }
  },
  computed: {},
  watch: {},
  created() {
    this.getKey()
  },
  mounted: function () {
    window.addEventListener(
      'message',
      function (event) {
        // 接收位置信息，用户选择确认位置点后选点组件会触发该事件，回传用户的位置信息
        var loc = event.data
        if (loc && loc.module === 'locationPicker') {
          // 防止其他应用也会向该页面post信息，需判断module是否为'locationPicker'
          window.parent.selectAdderss(loc)
        }
      },
      false
    )
    window.selectAdderss = this.selectAdderss
  },
  methods: {
    // 选择经纬度
    selectAdderss(data) {
      this.valueModel = data.latlng.lat + ',' + data.latlng.lng
      this.modalMap = false
      this.$emit('changeValue', { field: this.field, value: this.valueModel })
    },
    // 查找位置
    onSearch(value) {
      this.modalMap = true
      this.valueModel = ''
    },
    // mapkey值
    getKey() {
      keyApi()
        .then(async (res) => {
          let keys = res.data.key
          this.keyUrl = `https://apis.map.qq.com/tools/locpicker?type=1&key=${keys}&referer=myapp`
        })
        .catch((res) => {
          this.$Message.error(res.msg)
        })
    },
  },
}
</script>
<style scoped lang="less">
@import url('./css/build.css');
.inputW {
  width: 400px;
}
</style>