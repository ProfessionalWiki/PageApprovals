<template>
	<template v-if="pageApproved">
		<cdx-info-chip
			v-tooltip:left-start="tooltipText"
			status="success"
			@mouseenter="updateTooltip"
		>
			{{ $i18n( 'pageapprovals-status-approved' ) }}
		</cdx-info-chip>
	</template>
	<template v-else>
		<cdx-info-chip
			status="warning"
		>
			{{ $i18n( 'pageapprovals-status-not-approved' ) }}
		</cdx-info-chip>
	</template>
</template>

<script>
const { defineComponent, ref, watch } = require( 'vue' );
const { CdxInfoChip, CdxTooltip } = require( '../../codex.js' );
const { getRelativeTimestamp, getLocalizedDateTime } = require( '../utils/time.js' );

module.exports = defineComponent( {
	name: 'StatusDisplay',
	components: { CdxInfoChip },
	directives: {
		tooltip: CdxTooltip
	},
	props: {
		pageApproved: {
			type: Boolean,
			required: true
		},
		approvalTimestamp: {
			type: String,
			default: null
		},
		approver: {
			type: String,
			default: null
		}
	},
	setup( props ) {
		const tooltipText = ref( '' );

		// Update the relative timestamp in the tooltip on mouse enter
		const updateTooltip = () => {
			if ( !props.pageApproved ) {
				tooltipText.value = '';
				return;
			}
			const approvedAgo = getRelativeTimestamp( props.approvalTimestamp );
			tooltipText.value = mw.msg( 'pageapprovals-approve-page-text',
				props.approver,
				approvedAgo,
				getLocalizedDateTime( props.approvalTimestamp )
			);
		};

		// Set initial value for tooltip and update when props change
		watch( () => props.pageApproved, updateTooltip, { immediate: true } );

		return {
			tooltipText,
			updateTooltip
		};
	}
} );
</script>

<style lang="less">
@import 'mediawiki.skin.variables.less';

.ext-pageapprovals {
	.cdx-info-chip {
		font-weight: @font-weight-normal;
	}
}
</style>
