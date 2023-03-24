<template>
    <div class="i-layout-menu-head" :class="{ 'i-layout-menu-head-mobile': isMobile }">
      <template v-if="!isMobile && !isMenuLimit">
        <Tabs
        :value="headerName"
        :animated="false"
        @on-click="tabClick"
      >
        <TabPane
          v-for="(tab, index) in filterHeader"
          :key="index"
          :label="tab.title"
          :name="tab.header"
        ></TabPane>
      </Tabs>
      </template>
      <div class="i-layout-header-trigger i-layout-header-trigger-min i-layout-header-trigger-in i-layout-header-trigger-no-height" v-else>
          <Dropdown trigger="click" :class="{ 'i-layout-menu-head-mobile-drop': isMobile }">
              <Icon type="ios-apps" />
              <DropdownMenu slot="list">
                  <i-link v-for="item in filterHeader" :to="item.path"  :key="item.path">
                      <DropdownItem>
                          <i-menu-head-title :item="item" />
                      </DropdownItem>
                  </i-link>
              </DropdownMenu>
          </Dropdown>
      </div>
    </div>
</template>
<script>
    import { mapState, mapGetters } from 'vuex';
    import { getStyle } from 'view-design/src/utils/assist';
    import { on, off } from 'view-design/src/utils/dom';
    import { throttle } from 'lodash';

    export default {
        name: 'iMenuHead',
        computed: {
            ...mapState('admin/layout', [
                'isMobile'
            ]),
            ...mapState('admin/menu', [
                'headerName'
            ]),
            ...mapGetters('admin/menu', [
                'filterHeader'
            ])
        },
        data () {
            return {
                handleResize: () => {},
                isMenuLimit: false,
                menuMaxWidth: 0 // 达到这个值后，menu 就显示不下了
            }
        },
        methods: {
            handleGetMenuHeight () {
                const menuWidth = parseInt(getStyle(this.$el, 'width'));
                const $menu = this.$refs.menu;
                if ($menu) {
                    const menuHeight = parseInt(getStyle(this.$refs.menu.$el, 'height'));
                    if (menuHeight > 64) {
                        if (!this.isMenuLimit) {
                            this.menuMaxWidth = menuWidth;
                        }
                        this.isMenuLimit = true;
                    }
                } else if (menuWidth > this.menuMaxWidth) {
                    this.isMenuLimit = false;
                }else{
									this.isMenuLimit = true;
								}
            },
            tabClick(name) {
              let tab = this.filterHeader.find(item => item.header === name);
              this.$router.push(tab.path);
            }
        },
        watch: {
            filterHeader () {
                this.handleGetMenuHeight();
            },
            isMobile () {
                this.handleGetMenuHeight();
            }
        },
        mounted () {
            this.handleResize = throttle(this.handleGetMenuHeight, 100, { leading: false });
            on(window, 'resize', this.handleResize);
            this.handleGetMenuHeight();
        },
        beforeDestroy () {
            off(window, 'resize', this.handleResize);
        }
    }
</script>
<style scoped lang="stylus">
	.ivu-menu-horizontal .ivu-menu-item{
		padding 0 10px;
	}
  .i-layout-menu-head {
    min-width: 0;
  }
  .ivu-tabs {
    color: rgba(255, 255, 255, 0.7);
  }
  >>>.ivu-tabs-bar {
    border-bottom: 0;
    margin-bottom: 0;
  }
  >>>.ivu-tabs-nav .ivu-tabs-tab {
    height: 66px;
    padding: 0 10px;
    line-height: 66px;
  }
  >>>.ivu-tabs-nav .ivu-tabs-tab-active {
    font-weight: 800;
    color: #FFFFFF;
  }
  >>>.ivu-tabs-ink-bar {
    background-color: #FFFFFF;
    bottom: 2px;
  }
  >>>.ivu-tabs-nav .ivu-tabs-tab:hover {
    font-weight: 800;
    color: #FFFFFF !important;
  }
  >>>.ivu-tabs-nav .ivu-tabs-tab:active {
    font-weight: 800;
    color: #FFFFFF !important;
  }
  >>>.ivu-tabs-nav-prev, >>>.ivu-tabs-nav-next {
    z-index: 2;
    line-height: 64px;
  }
</style>
