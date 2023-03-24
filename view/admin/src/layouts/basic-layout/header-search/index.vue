<template>
  <!--<span-->
    <!--v-if="isDesktop"-->
    <!--class="i-layout-header-trigger i-layout-header-trigger-min i-layout-header-trigger-in i-layout-header-trigger-nohover"-->
  <!--&gt;-->
    <!--&lt;!&ndash;<input class="i-layout-header-search" type="text" :placeholder="$t('basicLayout.search.placeholder')">&ndash;&gt;-->
    <!--<Select-->
      <!--v-model="currentVal"-->
      <!--placeholder="搜索..."-->
      <!--filterable-->
      <!--remote-->
      <!--style="width:150px"-->
      <!--:remote-method="remoteMethod"-->
      <!--:loading="loading"-->
    <!--&gt;-->
      <!--<Option-->
        <!--v-for="(option, index) in menusList"-->
        <!--:value="option.menu_path"-->
        <!--:key="index"-->
        <!--:disabled="option.type === 1"-->
        <!--&gt;{{ option.menu_name }}</Option-->
      <!--&gt;-->
    <!--</Select>-->
  <!--</span>-->
  <span class="i-layout-header-trigger i-layout-header-trigger-min">
    <Dropdown
      trigger="click"
      class="i-layout-header-search-drop"
      ref="dropdown"
    >
      <Icon type="ios-search" />
      <DropdownMenu slot="list">
        <div class="i-layout-header-search-drop-main">
          <!--<Input size="large" prefix="ios-search" type="text" :placeholder="$t('basicLayout.search.placeholder')" />-->
          <Select
            v-model="currentVal"
            placeholder="搜索..."
            filterable
            size="large"
            remote
            :remote-method="remoteMethod"
            :loading="loading"
          >
            <Option
              v-for="(option, index) in menusList"
              :value="option.menu_path"
              :key="index"
              :disabled="option.type === 1"
              >{{ option.menu_name }}</Option
            >
          </Select>
          <span
            class="i-layout-header-search-drop-main-cancel"
            @click="handleCloseSearch"
            >{{ $t("basicLayout.search.cancel") }}</span
          >
        </div>
      </DropdownMenu>
    </Dropdown>
  </span>
</template>
<style lang="less" scoped>
  /deep/.ivu-select-dropdown-list{
    max-height: 200px!important;
    overflow-y: auto!important;
  }
</style>
<style>
.i-layout-header-trigger .ivu-select-selection {
  border: 0 !important;
}
.i-layout-header-trigger .ivu-select-visible .ivu-select-selection {
  box-shadow: unset !important;
}
.ivu-select-input {
  font-size: 13px !important;
}
.i-layout-header-trigger li.ivu-select-item {
  text-align: left;
}
</style>
<script>
import { mapState } from "vuex";
import { menusListApi } from "@/api/account";

export default {
  name: "iHeaderSearch",
  data() {
    return {
      currentVal: "",
      loading: false,
      menusList: [],
    };
  },
  computed: {
    ...mapState("admin/layout", ["isDesktop", "headerMenu"]),
  },
  created() {
    this.getMenusList();
  },
  methods: {
    handleCloseSearch() {
      this.$refs.dropdown.handleClick();
    },
    getMenusList() {
      this.loading = true;
      menusListApi().then((res) => {
        this.loading = false;
        this.menusList = res.data;
      });
    },
    remoteMethod() {
      this.$router.push({ path: this.currentVal });
    },
  },
};
</script>
