<style lang="scss" scoped>
.avatar {
  border: 1px solid #e1e4e8 !important;
  padding: 10px;
  background-color: #fff;
  border-radius: 7px;

  img {
    width: 100%;
    height: auto;
  }
}
</style>

<template>
  <div class="db center mb4 avatar">
    <uploadcare :public-key="uploadcarePublicKey"
                :tabs="'file'"
                @success="onSuccess"
                @error="onError"
    >
      <the-avatar :avatar="localAvatar" :size="300" />
    </uploadcare>
  </div>
</template>

<script>
import Uploadcare from 'uploadcare-vue/src/Uploadcare.vue';
import TheAvatar from '@/Shared/Avatar';

export default {
  components: {
    Uploadcare,
    TheAvatar,
  },

  props: {
    employee: {
      type: Object,
      default: null,
    },
    permissions: {
      type: Object,
      default: null,
    },
    uploadcarePublicKey: {
      type: String,
      default: null,
    },
  },

  data() {
    return {
      localAvatar: null,
      form: {
        uuid: null,
        name: null,
        original_url: null,
        cdn_url: null,
        mime_type: null,
        size: null,
        errors: [],
      },
      loadingState: '',
      errorTemplate: Error,
    };
  },

  mounted() {
    this.localAvatar = this.employee.avatar;

    if (localStorage.success) {
      this.flash(localStorage.success, 'success');

      localStorage.removeItem('success');
    }
  },

  methods: {
    onSuccess(file) {
      this.value = file.cdnUrl;
      this.form.uuid = file.uuid;
      this.form.name = file.name;
      this.form.original_url = file.originalUrl;
      this.form.cdn_url = file.cdnUrl;
      this.form.mime_type = file.mimeType;
      this.form.size = file.size;

      this.importAvatar();
    },

    onError() {
    },

    importAvatar() {
      this.loadingState = 'loading';

      axios.put(`/${this.$page.props.auth.company.id}/employees/${this.employee.id}/avatar/update`, this.form)
        .then(response => {
          this.localAvatar = response.data.avatar;
        })
        .catch(error => {
          this.loadingState = null;
          this.form.errors = error.response.data;
        });
    },
  }
};

</script>
