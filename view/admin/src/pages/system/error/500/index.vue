<template>
    <div>
        <Exception type="500" img-color :desc="$t('page.exception.e500')" :back-text="$t('page.exception.btn')" :redirect="indexPath" />
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
