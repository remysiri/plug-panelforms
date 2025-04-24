<template>
	<k-inside>
		<k-view class="k-panelforms-view">
			<k-header>Web Forms</k-header>
			
			<k-tabs :tabs="formattedTabs" :tab="currentForm" />
			
			<template v-if="currentForm">
				<k-table
					:columns="columns"
					:index="index"
					:rows="paginatedItems"
					@cell="onCell"
				>
					<template #options="{ row }">
						<k-options-dropdown :options="options(row)" />
					</template>
				</k-table>

				<footer class="k-bar">
					<k-pagination
						v-bind="pagination"
						:details="true"
						:total="submissions.length"
						@paginate="pagination.page = $event.page"
					/>
				</footer>
			</template>
		</k-view>
	</k-inside>
</template>

<script>
export default {
	props: {
		submissions: {
			type: Array,
			default: () => []
		},
		tabs: {
			type: Array,
			default: () => []
		},
		currentForm: {
			type: String,
			default: null
		}
	},
	data() {
		return {
			sortDirection: 'asc',
			pagination: {
				page: 1,
				limit: 20,
			}
		};
	},
	computed: {
		index() {
			return (this.pagination.page - 1) * this.pagination.limit + 1;
		},
		paginatedItems() {
			return this.submissions.slice(this.index - 1, this.pagination.limit * this.pagination.page);
		},
		columns() {
			return {
				timestamp: {
					label: 'Datum',
					type: "text",
					width: "3/6",
				},
				email: {
					label: 'Email',
					type: "link",
					width: "3/6",
				},
				
			};
		},
		formattedTabs() {
			if (!Array.isArray(this.tabs)) {
				return [];
			}
			
			return this.tabs.map(tab => ({
				name: tab.name,
				label: tab.name,
				link: tab.link
			}));
		},
	},
	methods: {
		onCell({ row, columnIndex }) {
			this.$panel.drawer.open({
				component: 'k-text-drawer',
				props: {
					icon: 'info',
					title: row.timestamp,
					text: this.formatSubmissionData(row.data)
				}
			})
		},
		options(submission) {
			return [
				{
					label: 'Weergeven',
					icon: 'info',
					click: () => this.$panel.drawer.open({
						component: 'k-text-drawer',
						props: {
							icon: 'info',
							title: submission.timestamp,
							text: this.formatSubmissionData(submission.data)
						}
					})
				}
			];
		},
		formatSubmissionData(data) {
			let formattedText = '';
			
			const filteredData = Object.entries(data).filter(([key]) => key !== 'email');
			
			filteredData.forEach(([key, value]) => {
				const readableKey = key
					.replace(/([A-Z])/g, ' $1')
					.replace(/^./, str => str.toUpperCase())
					.replace(/_/g, ' ');
				
				formattedText += `<strong>${readableKey}:</strong><br>${value}<br><br>`;
			});
			
			return formattedText;
		}
	}
}
</script>

<style>
	.k-panelforms-view .k-table-column {
		cursor: pointer;
	}
	.k-panelforms-view .k-bar {
		margin-top: 10px;
	}
</style>