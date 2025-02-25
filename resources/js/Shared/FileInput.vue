<style lang="scss" scoped>
.error-explanation {
  background-color: #fde0de;
  border-color: rgb(226, 171, 167);
}

.error {
  &:focus {
    box-shadow: 0 0 0 1px #fff9f8;
  }
}

.optional-badge {
  border-radius: 4px;
  color: #283e59;
  background-color: #edf2f9;
  padding: 3px 4px;
}

</style>

<template>
  <div :class="extraClassUpperDiv">
    <label v-if="label" class="db fw4 lh-copy f6" :for="id">
      {{ label }}
      <span v-if="!required" class="optional-badge f7">
        {{ $t('app.optional') }}
      </span>
    </label>
    <input :id="realId"
           :ref="customRef"
           v-bind="$attrs"
           type="file"
           class="br2 f5 w-100 ba b--black-40 pa2 outline-0"
           :required="required"
           :name="name"
           :autofocus="autofocus"
           :value="value"
           :data-cy="datacy"
           @change="$emit('update:modelValue', $event.target.value)"
    />
    <div v-if="hasError" class="error-explanation pa3 ba br3 mt1">
      {{ errors[0] }}
    </div>
    <p v-if="help" class="f7 mb3 lh-copy">
      {{ help }}
    </p>
  </div>
</template>

<script>
export default {
  inheritAttrs: false,

  model: {
    prop: 'modelValue',
    event: 'update:modelValue'
  },

  props: {
    id: {
      type: String,
      default: 'text-file-input-',
    },
    modelValue: {
      type: String,
      default: '',
    },
    customRef: {
      type: String,
      default: 'input',
    },
    name: {
      type: String,
      default: 'input',
    },
    errors: {
      type: Array,
      default: () => [],
    },
    datacy: {
      type: String,
      default: '',
    },
    help: {
      type: String,
      default: '',
    },
    label: {
      type: String,
      default: '',
    },
    required: {
      type: Boolean,
      default: false,
    },
    extraClassUpperDiv: {
      type: String,
      default: 'mb3',
    },
    autofocus: {
      type: Boolean,
      default: false,
    }
  },

  emits: [
    'update:modelValue'
  ],

  data() {
    return {
      localErrors: [],
    };
  },

  computed: {
    realId() {
      return this.id + this._.uid;
    },

    hasError() {
      return this.errors.length > 0 && this.required;
    }
  },

  watch: {
    errors(value) {
      this.localErrors = value;
    },
  },

  mounted() {
    this.localErrors = this.errors;
  },

  methods: {
    focus() {
      this.$refs.input.focus();
    },

    select() {
      this.$refs.input.select();
    },
  },
};
</script>
