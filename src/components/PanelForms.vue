<template>
	<k-panel-inside>
		<k-view class="k-panelforms-view">
			<k-header>Web Forms</k-header>
			
			<k-tabs :tabs="formattedTabs" :tab="currentForm" />
			
			<template v-if="currentForm">
				<k-bar align="end" class="k-panelforms-export-bar">
					<k-button variant="filled" class="k-panelforms-export" @click="exportToExcel"><k-icon type="download" />Export</k-button>
				</k-bar>
				
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
	</k-panel-inside>
</template>

<script>
import * as XLSX from 'xlsx';

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
		},
		exportToExcel() {
			if (!this.submissions.length) return;

			// Prepare the data for export
			const exportData = this.submissions.map(submission => {
				const row = {
					'Date': submission.timestamp,
					'Email': submission.data.email
				};

				// Add all other form fields
				Object.entries(submission.data).forEach(([key, value]) => {
					if (key !== 'email') {
						const readableKey = key
							.replace(/([A-Z])/g, ' $1')
							.replace(/^./, str => str.toUpperCase())
							.replace(/_/g, ' ');
						row[readableKey] = value;
					}
				});

				return row;
			});

			// Create worksheet
			const ws = XLSX.utils.json_to_sheet(exportData);
			
			// Create workbook
			const wb = XLSX.utils.book_new();
			XLSX.utils.book_append_sheet(wb, ws, 'Form Data');

			// Generate file and trigger download
			const fileName = `${this.currentForm}_export_${new Date().toISOString().split('T')[0]}.xlsx`;
			XLSX.writeFile(wb, fileName);
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
	.k-panelforms-export-bar {
		margin-block: 15px;
	}
	.k-panelforms-export  .k-button-text{
		display: flex;
		align-items: center;
		gap: 10px;
	}
</style>