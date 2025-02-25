<style lang="scss" scoped>
.edit-skill-item:first-child {
  border-top-width: 1px;
  border-top-style: solid;
  border-top-left-radius: 3px;
  border-top-right-radius: 3px;
}

.edit-skill-item:last-child {
  border-bottom-left-radius: 3px;
  border-bottom-right-radius: 3px;
}

.ball-pulse {
  right: 8px;
  top: 38px;
  position: absolute;
}

.skill {
  font-size: 12px;
  border: 1px solid transparent;
  border-radius: 2em;
  padding: 3px 10px;
  line-height: 22px;
  color: #0366d6;
  background-color: #f1f8ff;

  &:hover {
    background-color: #def;
  }
}

.existing-skill:last-child {
  margin-bottom: 0;
}

.plus-button {
  padding: 0px 4px 2px 4px;
  margin-right: 2px;
  border-color: #60995c;
  color: #60995c;
  font-size: 11px;
  top: -3px;
  position: relative;
}
</style>

<template>
  <div class="mb4 relative">
    <span class="db fw5 mb2">
      <span class="mr1">
        🧠
      </span> {{ $t('employee.skills_title') }}

      <help :url="$page.props.help_links.skills" :datacy="'help-icon-skills'" />
    </span>
    <img v-if="permissions.can_manage_skills && !editMode" loading="lazy" src="/img/edit_button.svg" class="box-plus-button absolute br-100 pa2 bg-white pointer" data-cy="manage-skill-button"
         width="22"
         height="22" alt="manage skills"
         @click.prevent="toggleEditMode()"
    />

    <!-- Blank state -->
    <div v-if="localSkills.length == 0 && !editMode" data-cy="skill-list-blank" class="br3 bg-white box z-1 pa3">
      <p class="mb0 mt0 lh-copy f6">
        {{ $t('employee.skills_no_skill_yet') }}
      </p>
    </div>

    <!-- List of existing skills -->
    <div v-if="localSkills.length > 0 && !editMode" class="br3 bg-white box z-1 pa3" data-cy="list-skills">
      <ul class="list mv0 pl0">
        <li v-for="skill in localSkills" :key="skill.id" class="relative dib fw5 mr2 mb2 existing-skill" :data-cy="'non-edit-skill-list-item-' + skill.id">
          <inertia-link :href="skill.url" class="skill no-underline">{{ skill.name }}</inertia-link>
        </li>
      </ul>
    </div>

    <!-- Edit skills -->
    <div v-if="editMode" class="br3 bg-white box z-1 pa3">
      <form @submit.prevent="search">
        <div class="relative">
          <text-input :ref="'searchSkillInput'"
                      v-model="form.searchTerm"
                      :errors="$page.props.errors.lastname"
                      :label="$t('employee.skills_search_term')"
                      :required="true"
                      :datacy="'search-skill'"
                      :extra-class-upper-div="'mb0'"
                      @keyup="search"
                      @update:model-value="search"
                      @esc-key-pressed="toggleEditMode()"
          />
          <ball-pulse-loader v-if="processingSearch" color="#5c7575" size="7px" />
        </div>
      </form>

      <!-- search results -->
      <p v-if="searchProcessed && !skillAlreadyDefined && searchResults.length == 0" data-cy="submit-create-skill" class="mt0 bb br bl bb-gray pa2 bb-gray-hover pointer relative" @click.prevent="create(form.searchTerm)">
        <span class="ba br-100 plus-button">+</span> {{ $t('employee.skills_create', { name: form.searchTerm }) }}
      </p>
      <p v-if="skillAlreadyDefined && searchResults.length == 0" data-cy="skill-already-in-list" class="list bb br bl bb-gray pa2 ma0">
        {{ $t('employee.skills_already_have_skill', { name: form.searchTerm }) }}
      </p>
      <ul v-if="searchResults.length > 0" class="list pl0 mt0 mb2">
        <li v-for="skill in searchResults" :key="skill.id" class="bb br bl bb-gray pa2 bb-gray-hover pointer relative" :data-cy="'skill-name-' + skill.id" @click.prevent="create(skill.name)">
          <span class="ba br-100 plus-button">+</span> {{ skill.name }}
        </li>
        <li v-if="!skillAlreadyDefined" class="bb br bl bb-gray pa2 bb-gray-hover pointer relative" data-cy="submit-create-skill-list-of-skills" @click.prevent="create(form.searchTerm)">
          <span class="ba br-100 plus-button">+</span> {{ $t('employee.skills_create', { name: form.searchTerm }) }}
        </li>
      </ul>

      <!-- existing skills -->
      <p v-if="localSkills.length > 0" class="mb2 f6 fw5">⭐️ {{ $t('employee.skills_list') }}</p>
      <ul class="pl0 list mt0">
        <li v-for="skill in localSkills" :key="skill.id" class="bb br bl bb-gray pa2 edit-skill-item bb-gray-hover" :data-cy="'existing-skills-list-item-' + skill.id">
          {{ skill.name }}
          <span class="fr">
            <a href="#" :data-cy="'existing-skills-list-item-remove-' + skill.id" class="f6 bb b--dotted bt-0 bl-0 br-0 pointer c-delete" @click.prevent="remove(skill)">{{ $t('app.remove') }}</a>
          </span>
        </li>
      </ul>

      <!-- exit edit mode -->
      <div class="tr">
        <a class="btn dib tc w-auto-ns w-100 mb2 pv2 ph3" data-cy="cancel-add-skill" @click="editMode = false">
          {{ $t('app.exit_edit_mode') }}
        </a>
      </div>
    </div>
  </div>
</template>

<script>
import TextInput from '@/Shared/TextInput';
import BallPulseLoader from 'vue-loaders/dist/loaders/ball-pulse';
import Help from '@/Shared/Help';

export default {
  components: {
    'ball-pulse-loader': BallPulseLoader.component,
    TextInput,
    Help,
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
    skills: {
      type: Array,
      default: null,
    },
  },

  data() {
    return {
      editMode: false,
      processingSearch: false,
      searchProcessed: false,
      searchResults: [],
      skillAlreadyDefined: false,
      localSkills: [],
      form: {
        searchTerm: null,
        errors: [],
      },
    };
  },

  created() {
    this.localSkills = this.skills;
  },

  methods: {
    toggleEditMode() {
      this.editMode = !this.editMode;
      this.searchTerm = null;

      this.$nextTick(() => {
        this.$refs.searchSkillInput.focus();
      });
    },

    search: _.debounce(
      function () {
        this.skillAlreadyDefined = false;

        if (this.form.searchTerm !== '') {
          if (this.localSkills.some(skill => skill.name === this.form.searchTerm)) {
            this.skillAlreadyDefined = true;
          }

          this.processingSearch = true;
          this.searchProcessed = false;
          this.searchResults = [];

          axios.post(this.route('skills.search', [this.$page.props.auth.company.id, this.employee.id]), this.form)
            .then(response => {
              this.processingSearch = false;
              this.searchProcessed = true;

              if (response.data.data.length > 0) {
                this.searchResults = _.filter(response.data.data, skill => _.every(this.localSkills, s => skill.name !== s.name));
              }
            })
            .catch(error => {
              this.form.errors = error.response.data;
              this.processingSearch = false;
              this.skillAlreadyDefined = false;
            });
        } else {
          this.searchProcessed = false;
        }
      }, 500),

    create(name) {
      this.searchProcessed = false;
      this.searchResults = [];
      this.form.searchTerm = null;
      this.skillAlreadyDefined = false;

      axios.post(this.route('skills.store', [this.$page.props.auth.company.id, this.employee.id]), { searchTerm: name })
        .then(response => {
          this.localSkills.push(response.data.data);
        })
        .catch(error => {
          this.form.errors = error.response.data;
          this.processingSearch = false;
        });
    },

    remove(skill) {
      this.form.searchTerm = name;

      axios.delete(this.route('skills.destroy', [this.$page.props.auth.company.id, this.employee.id, skill.id]))
        .then(response => {
          var changedId = this.localSkills.findIndex(x => x.id === skill.id);
          this.localSkills.splice(changedId, 1);
        })
        .catch(error => {
          this.form.errors = error.response.data;
          this.processingSearch = false;
        });
    }
  }
};
</script>
