<style lang="scss" scoped>
.list li:last-child {
  border-bottom: 0;
}

.type {
  font-size: 12px;
  border: 1px solid transparent;
  border-radius: 6px;
  padding: 2px 6px;
  line-height: 22px;
  color: #0366d6;
  background-color: #f1f8ff;
  top: -1px;

  &:hover {
    background-color: #def;
  }

  a:hover {
    border-bottom: 0;
  }

  &.failed {
    background-color: #ea8383;
    color: #fff;
  }

  span {
    border-radius: 100%;
    background-color: #c8dcf0;
    padding: 0px 5px;
  }
}

.check {
  width: 17px;
  top: 4px;
}
</style>

<template>
  <layout :notifications="notifications">
    <div class="ph2 ph0-ns">
      <!-- BREADCRUMB -->
      <div class="mt4-l mt1 mw6 br3 bg-white box center breadcrumb relative z-0 f6 pb2">
        <ul class="list ph0 tc-l tl">
          <li class="di">
            <inertia-link :href="'/' + $page.props.auth.company.id + '/dashboard'">{{ $t('app.breadcrumb_dashboard') }}</inertia-link>
          </li>
          <li class="di">
            ...
          </li>
          <li class="di">
            <inertia-link :href="'/' + $page.props.auth.company.id + '/account/employees'">{{ $t('app.breadcrumb_account_manage_employees') }}</inertia-link>
          </li>
          <li class="di">
            {{ $t('app.breadcrumb_account_manage_past_archives') }}
          </li>
        </ul>
      </div>

      <!-- BODY -->
      <div class="mw7 center br3 mb5 bg-white box restricted relative z-1">
        <div class="pa3 mt5">
          <h2 class="tc normal mb4">
            {{ $t('account.import_employees_archives_title') }}

            <help :url="$page.props.help_links.import_employees" :top="'1px'" />
          </h2>

          <p class="tr">
            <inertia-link :href="importJobs.url_new" class="btn relative dib-l db">
              {{ $t('account.import_employees_archives_cta') }}
            </inertia-link>
          </p>

          <!-- LIST OF JOB REPORTS -->
          <ul v-if="importJobs.entries.length > 0" class="list pl0 mv0 center ba br2 bb-gray" data-cy="statuses-list" :data-cy-items="importJobs.entries.map(n => n.id)">
            <li v-for="job in importJobs.entries" :key="job.id" class="pa3 bb bb-gray bb-gray-hover flex justify-between items-center">
              <div class="di relative">
                <span class="db mb2">{{ $t('account.import_employees_archives_item_title', { count: job.number_of_entries }) }}
                  <span v-if="job.status == 'imported'" :class="job.status" class="type relative">
                    <svg class="check relative" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    {{ job.status_translated }}
                  </span>
                </span>
                <span class="db f7">{{ $t('account.import_employees_archives_item_date', { date: job.import_started_at, author: job.author.name }) }}</span>
              </div>

              <!-- LIST OF ACTIONS FOR EACH REPORT -->
              <ul class="list pa0 ma0 di-ns db fr-ns mt2 mt0-ns f6">
                <li><inertia-link :href="job.url">{{ $t('app.view') }}</inertia-link></li>
              </ul>
            </li>
          </ul>

          <!-- BLANK STATE -->
          <div v-if="importJobs.entries.length == 0" class="pa3 mt5">
            <p class="tc measure center mb4 lh-copy">
              {{ $t('account.import_employees_archives_blank_description') }}
            </p>
            <img loading="lazy" src="/img/streamline-icon-document-box-3@140x140.png" alt="add email symbol" class="db center mb4" height="80"
                 width="80"
            />
          </div>
        </div>
      </div>
    </div>
  </layout>
</template>

<script>
import Layout from '@/Shared/Layout';
import Help from '@/Shared/Help';

export default {
  components: {
    Layout,
    Help,
  },

  props: {
    notifications: {
      type: Array,
      default: null,
    },
    importJobs: {
      type: Object,
      default: null,
    },
  },

  data() {
    return {
    };
  },

  mounted() {
    if (localStorage.success) {
      this.flash(localStorage.success, 'success');

      localStorage.removeItem('success');
    }
  },

  methods: {
  }
};

</script>
