<template>
    <div class="mobile-config">
        <div  v-for="(item,key) in rCom" :key="key">
            <component :is="item.components.name" :configObj="configObj" ref="childData" :configNme="item.configNme" :key="key" :index="activeIndex" :num="item.num"></component>
        </div>
        <rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn>
    </div>
</template>

<script>
import toolCom from '@/components/mobileConfigRight/index.js';
import rightBtn from '@/components/rightBtn/index.vue';
import { mapState, mapMutations, mapActions } from 'vuex';
export default {
  name: 'c_home_comb',
  componentsName: 'home_comb',
  components: {
    ...toolCom,
    rightBtn
  },
  props: {
    activeIndex: {
      type: null
    },
    num: {
      type: null
    },
    index: {
      type: null
    }
  },
  data() {
    return {
      configObj: {},
      rCom: [
        {
          components: toolCom.c_tab,
          configNme: 'tabConfig'
        }
      ]
    };
  },
  watch: {
    num(nVal) {
      const value = JSON.parse(JSON.stringify(this.$store.state.admin.mobildConfig.defaultArray[nVal]));
      this.configObj = value;
    },
    configObj: {
      handler(nVal, oVal) {
        this.$store.commit('admin/mobildConfig/UPDATEARR', { num: this.num, val: nVal });
      },
      deep: true
    },
    'configObj.tabConfig.tabVal': {
      handler(nVal, oVal) {
        var arr = [this.rCom[0]];
        if (nVal == 0) {
          const tempArr = [
            {
              components: toolCom.c_upload_img,
              configNme: 'logoConfig'
            },
            {
              components: toolCom.c_hot_word,
              configNme: 'hotWords'
            },
            {
              components: toolCom.c_input_number,
              configNme: 'numConfig'
            }
          ];
          this.rCom = arr.concat(tempArr);
        } else if (nVal == 1) {
          const tempArr = [
            {
              components: toolCom.c_is_show,
              configNme: 'classShow'
            },
            {
              components: toolCom.c_bg_color,
              configNme: 'txtColor'
            },
            {
              components: toolCom.c_bg_color,
              configNme: 'classColor'
            },
            {
              components: toolCom.c_bg_color,
              configNme: 'bgColor'
            }
          ];
          this.rCom = arr.concat(tempArr);
        } else {
          const tempArr = [
            {
              components: toolCom.c_menu_list,
              configNme: 'swiperConfig'
            }
          ];
          this.rCom = arr.concat(tempArr);
        }
      },
      deep: true
    }
  },
  mounted() {
    this.$nextTick(() => {
      const value = JSON.parse(JSON.stringify(this.$store.state.admin.mobildConfig.defaultArray[this.num]));
      this.configObj = value;
    });
  },
  methods: {
    handleSubmit(name) {
      const obj = {};
      obj.activeIndex = this.activeIndex;
      obj.data = this.configObj;
      this.add(obj);
    },
    ...mapMutations({
      add: 'admin/mobildConfig/UPDATEARR'
    })
  }
};
</script>

<style scoped lang="stylus">
    .title-tips
        padding-bottom 10px
        font-size 14px
        color #333
        span
            margin-right 14px
            color #999
</style>
