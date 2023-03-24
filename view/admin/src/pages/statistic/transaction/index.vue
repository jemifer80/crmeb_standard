<template>
<!-- 运营-交易统计 -->
    <div>
        <to-day></to-day>
        <transaction-from></transaction-from>
    </div>
</template>

<script>
    import toDay from './components/toDay'
    import transactionFrom from './components/transaction'
    export default {
        name: "index",
        components: {
            toDay,
            transactionFrom
        },
    }
</script>

<style scoped>

</style>
