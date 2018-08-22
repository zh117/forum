<script>
    import Replies from '../components/Replies';
    import SubscribeButton from '../components/SubscribeButton';

    export default {
        props: ['thread'],

        components: { Replies,SubscribeButton},

        data() {
            return {
                repliesCount:this.thread.replies_count,
                locked:this.thread.locked,
                title:this.thread.title,
                body:this.thread.body,
                form:{},// 改为 created() 设置初始值
                editing:false
            };
        },

        created() {
            this.resetForm();
        },

        methods: {
            toggleLock() {
                // 用变量表示路由
                let uri = `/locked-threads/${this.thread.slug}`;

                axios[this.locked ? 'delete' : 'post'](uri);

                this.locked = ! this.locked;
            },

            update() {
                // 用变量表示路由
                let uri = `/threads/${this.thread.channel.slug}/${this.thread.slug}`;

                axios.patch(uri,this.form).then(() => {
                    this.editing = false;
                    this.title = this.form.title;
                    this.body = this.form.body;

                    flash('Your thread has been updated.');
                });
            },

            resetForm() {
                this.form.title = this.thread.title;
                this.form.body = this.thread.body;

                this.editing = false;
            }
        }
    }
</script>