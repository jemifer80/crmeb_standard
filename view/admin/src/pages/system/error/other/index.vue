<template>
    <div>
        <Exception type="403" img-color desc="请使用 chrome / Microsoft Edge 等非IE浏览器打开" :back-text="$t('page.exception.btn')" :redirect="indexPath"/>
    </div>
</template>
<script>
    import { mapGetters } from "vuex";

    export default {
        data(){
            return {

            }
        },
        computed: {
            ...mapGetters('admin/menus', [
                'indexPath'
            ])
        },
    }
</script>
