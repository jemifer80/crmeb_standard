<template>
  <div>
    <FormItem :label="title" class="input-build" :class="getClassName()">
      <div class="input-error-wrapper">
        <TimePicker
          :type="timerType"
          :format="timerFormat"
          :value="valueModel"
          :placeholder="placeholder"
          v-model="valueModel"
          placement="bottom-end"
          @on-change="onchangeTime"
          class="inputW"
        ></TimePicker>
        <!--错误提醒-->
       <div v-if="errorMessage && !copy" class="error-wrapper">{{errorMessage}}</div>
      </div>
      <!--说明-->
      <div v-if="info" class="info-wrapper">{{ info }}</div>
    </FormItem>
  </div>
</template>

<script>
import build from './build'

export default {
  name: '',
  mixins: [build],

  props: {},
  data() {
    return {}
  },
  computed: {},
  watch: {},
  created() {},
  mounted() {},
  methods: {
    onchangeTime(e) {
      this.valueModel = e
       this.$emit('changeValue',{field: this.field, value: this.valueModel});
    },
  },
}
</script>
<style scoped>
@import url('./css/build.css');
.inputW {
  width: 400px;
}
</style>