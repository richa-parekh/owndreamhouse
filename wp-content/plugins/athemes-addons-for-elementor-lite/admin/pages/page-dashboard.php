<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$aafe_notifications     = $this->get_notifications();

/**
 * Hook 'athemes_addons_notifications_pro'
 * 
 * @since 1.0
 */
$aafe_notifications_pro = apply_filters( 'athemes_addons_notifications_pro', false );

/**
 * Hook 'athemes_addons_notification_tabs'
 * 
 * @since 1.0
 */
$aafe_notification_tabs = apply_filters( 'athemes_addons_notification_tabs', false );
$aafe_notification_read = $this->is_latest_notification_read();

$aafe_dashboard_tabs = $this->dashboard_tabs();

?>

<div class="athemes-addons-top-bar">
	<a href="<?php echo esc_url( athemes_addons_admin_upgrade_link( 'https://athemes.com/', array( 'utm_source' => 'plugin_dashboard', 'utm_medium' => 'athemes_addons_dashboard', 'utm_campaign' => 'aThemes_Addons' ), 'dashboard-top-bar-logo' ) ); ?>" class="athemes-addons-top-bar-logo" target="_blank">
		<svg width="96" height="24" viewBox="0 0 96 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path fill-rule="evenodd" clip-rule="evenodd" d="M23.4693 1.32313L8.45381 14.3107L0.67962 4.82163L23.4693 1.32313Z" fill="#335EEA"/>
			<path fill-rule="evenodd" clip-rule="evenodd" d="M23.2942 1.17329L8.23868 14.112L16.0129 23.601L23.2942 1.17329Z" fill="#BECCF9"/>
			<path fill-rule="evenodd" clip-rule="evenodd" d="M54.4276 12.8764C54.4276 10.7582 52.94 9.55047 51.2709 9.55047C49.6019 9.55047 48.8399 10.8325 48.8399 10.8325V4.53369H46.6629V18.5807H48.8399V12.7835C48.8399 12.7835 49.4205 11.6315 50.5453 11.6315C51.4886 11.6315 52.2506 12.1703 52.2506 13.4338V18.5807H54.4276V12.8764ZM39.9463 18.5807V7.6924H36.4449V5.57421H45.6247V7.6924H42.1233V18.5807H39.9463ZM36.604 12.8392C36.604 10.8325 35.1527 9.55047 32.6854 9.55047C30.8894 9.55047 29.2929 10.3494 29.2929 10.3494L30.0004 12.1889C30.0004 12.1889 31.3248 11.5386 32.5766 11.5386C33.3385 11.5386 34.427 11.9102 34.427 13.0622V13.5639C34.427 13.5639 33.6107 12.9321 32.0323 12.9321C30.1637 12.9321 28.658 14.1585 28.658 15.8493C28.658 17.7259 30.1456 18.8036 31.7602 18.8036C33.7014 18.8036 34.5903 17.4658 34.5903 17.4658V18.5807H36.604V12.8392ZM34.427 15.9236C34.427 15.9236 33.7376 16.9456 32.4314 16.9456C31.7602 16.9456 30.8713 16.7412 30.8713 15.8121C30.8713 14.8645 31.7965 14.5672 32.4677 14.5672C33.6469 14.5672 34.427 15.0132 34.427 15.0132V15.9236ZM59.7836 9.55047C62.142 9.55047 64.1195 11.4271 64.1195 14.1399C64.1195 14.3071 64.1195 14.6416 64.1013 14.976H57.6791C57.8424 15.7564 58.7314 16.7598 60.092 16.7598C61.5978 16.7598 62.4504 15.8679 62.4504 15.8679L63.5389 17.5401C63.5389 17.5401 62.1783 18.8036 60.092 18.8036C57.4796 18.8036 55.4659 16.7598 55.4659 14.177C55.4659 11.5943 57.2982 9.55047 59.7836 9.55047ZM61.9425 13.3595H57.6792C57.7517 12.5791 58.3867 11.5758 59.7836 11.5758C61.2168 11.5758 61.9062 12.5977 61.9425 13.3595ZM72.3963 11.0926C72.3987 11.0875 73.1253 9.55047 75.1357 9.55047C76.8773 9.55047 78.1472 10.7954 78.1472 12.9136V18.5807H75.9702V13.4896C75.9702 12.2818 75.5167 11.6315 74.4282 11.6315C73.2852 11.6315 72.741 12.7649 72.741 12.7649V18.5807H70.564V13.4896C70.564 12.2818 70.1104 11.6315 69.0219 11.6315C67.879 11.6315 67.3347 12.7649 67.3347 12.7649V18.5807H65.1577V9.77343H67.1896V11.0555C67.1896 11.0555 67.9697 9.55047 69.7294 9.55047C71.7947 9.55047 72.3946 11.0884 72.3963 11.0926ZM87.8391 14.1399C87.8391 11.4271 85.8616 9.55047 83.5032 9.55047C81.0178 9.55047 79.1855 11.5943 79.1855 14.177C79.1855 16.7598 81.1992 18.8036 83.8116 18.8036C85.8979 18.8036 87.2585 17.5401 87.2585 17.5401L86.17 15.8679C86.17 15.8679 85.3174 16.7598 83.8116 16.7598C82.451 16.7598 81.562 15.7564 81.3988 14.976H87.8209C87.8391 14.6416 87.8391 14.3071 87.8391 14.1399ZM81.3988 13.3595H85.6621C85.6258 12.5977 84.9364 11.5758 83.5032 11.5758C82.1063 11.5758 81.4713 12.5791 81.3988 13.3595ZM89.5486 15.5892L88.3331 17.2057C88.3331 17.2057 89.6937 18.8036 92.2154 18.8036C94.4106 18.8036 95.9708 17.5959 95.9708 16.0909C95.9708 14.2699 94.7553 13.6939 93.0499 13.3223C91.5986 13.0065 91.0181 12.8764 91.0181 12.3376C91.0181 11.7987 91.7619 11.5014 92.5783 11.5014C93.7393 11.5014 94.719 12.2632 94.719 12.2632L95.8075 10.6281C95.8075 10.6281 94.5194 9.55047 92.5783 9.55047C90.2198 9.55047 88.8773 10.9254 88.8773 12.3004C88.8773 13.9727 90.365 14.7159 92.0703 15.0875C93.3765 15.3662 93.7756 15.4777 93.7756 16.0537C93.7756 16.5925 93.0318 16.8527 92.1429 16.8527C90.6915 16.8527 89.5486 15.5892 89.5486 15.5892Z" fill="#101517"/>
		</svg>
	</a>
	<div class="athemes-addons-top-bar-infos">
		<div class="athemes-addons-top-bar-info-item">
			<div class="athemes-addons-version">
				<strong><?php echo esc_html( ( defined( 'ATHEMES_AFE_PRO_VERSION' ) ? ATHEMES_AFE_PRO_VERSION : ATHEMES_AFE_VERSION ) ); ?></strong>
				<span class="athemes-addons-badge<?php echo ( defined( 'ATHEMES_AFE_PRO_VERSION' ) ) ? ' athemes-addons-badge-pro' : ''; ?>">
					<?php echo esc_html( ( ! defined( 'ATHEMES_AFE_PRO_VERSION' ) ) ? __( 'FREE', 'athemes-addons-for-elementor-lite' ) : __( 'PRO', 'athemes-addons-for-elementor-lite' ) ); ?>
				</span>
			</div>
		</div>
		<div class="athemes-addons-top-bar-info-item">
			<a href="#" class="athemes-addons-notifications<?php echo esc_attr( ( $aafe_notification_read ) ? ' read' : '' ); ?>" title="<?php esc_html_e( 'aThemes Addons News', 'athemes-addons-for-elementor-lite' ); ?>">
				<span class="athemes-addons-notifications-count">1</span>
				<svg width="13" height="11" viewBox="0 0 10 8" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M8.86194 0.131242C8.75503 0.0584347 8.63276 0.0143876 8.50589 0.0029728C8.37902 -0.00844195 8.25143 0.0131252 8.13433 0.0657785L4.29726 1.65655C4.20642 1.69547 4.10927 1.71548 4.01119 1.71547H1.55473C1.34856 1.71547 1.15083 1.80168 1.00505 1.95514C0.859264 2.1086 0.777363 2.31674 0.777363 2.53377V2.59923H0V4.56315H0.777363V4.64825C0.782235 4.86185 0.866281 5.06498 1.01154 5.21422C1.1568 5.36346 1.35175 5.44697 1.55473 5.44691L2.48756 7.52866C2.55073 7.66885 2.65017 7.78744 2.77448 7.87081C2.89878 7.95418 3.04291 7.99896 3.1903 8H3.58209C3.78718 7.99827 3.98331 7.9113 4.12775 7.75802C4.27219 7.60475 4.35324 7.3976 4.35323 7.1817V5.52547L8.13433 7.11624C8.22733 7.1552 8.32652 7.17519 8.42662 7.17515C8.58191 7.17252 8.73314 7.12249 8.86194 7.03114C8.96423 6.95843 9.0486 6.86114 9.10808 6.7473C9.16755 6.63347 9.20043 6.50636 9.20398 6.3765V0.80552C9.20341 0.672312 9.17196 0.541263 9.11235 0.423757C9.05274 0.30625 8.96678 0.205839 8.86194 0.131242ZM3.57587 2.53377V4.64825H1.55473V2.53377H3.57587ZM3.57587 7.1817H3.18408L2.41915 5.44691H3.57587V7.1817ZM4.58333 4.74645C4.5095 4.70672 4.4325 4.67387 4.35323 4.64825V2.48794C4.43174 2.47089 4.50872 2.4468 4.58333 2.41593L8.42662 0.80552V6.35686L4.58333 4.74645ZM9.22264 2.76289V4.39949C9.42881 4.39949 9.62653 4.31327 9.77232 4.15981C9.9181 4.00635 10 3.79821 10 3.58119C10 3.36416 9.9181 3.15602 9.77232 3.00256C9.62653 2.8491 9.42881 2.76289 9.22264 2.76289Z" fill="#1E1E1E"/>
				</svg>
			</a>
		</div>
		<div class="athemes-addons-top-bar-info-item">
			<a href="<?php echo esc_url( athemes_addons_admin_upgrade_link( 'https://athemes.com/addons', array( 'utm_source' => 'plugin_dashboard', 'utm_medium' => 'athemes_addons_dashboard', 'utm_campaign' => 'aThemes_Addons' ), 'dashboard-website-link' ) ); ?>" class="athemes-addons-website" target="_blank">
				<?php esc_html_e( 'Website', 'athemes-addons-for-elementor-lite' ); ?>
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M13.6 2.40002H7.20002L8.00002 4.00002H11.264L6.39202 8.88002L7.52002 10.008L12 5.53602V8.00002L13.6 8.80002V2.40002ZM9.60002 9.60002V12H4.00002V6.40002H7.20002L8.80002 4.80002H2.40002V13.6H11.2V8.00002L9.60002 9.60002Z" fill="#3858E9"/>
				</svg>
			</a>
		</div>
	</div>
</div>

<div class="athemes-addons-notifications-sidebar">
	<a href="#" class="athemes-addons-notifications-sidebar-close" title="<?php echo esc_attr__( 'Close the sidebar', 'athemes-addons-for-elementor-lite' ); ?>">
		<svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M13.4584 4.54038L12.4597 3.54163L8.50008 7.50121L4.5405 3.54163L3.54175 4.54038L7.50133 8.49996L3.54175 12.4595L4.5405 13.4583L8.50008 9.49871L12.4597 13.4583L13.4584 12.4595L9.49883 8.49996L13.4584 4.54038Z" fill="black"/>
		</svg>
	</a>
	<div class="athemes-addons-notifications-sidebar-inner">
		<div class="athemes-addons-notifications-sidebar-header">
			<div class="athemes-addons-notifications-sidebar-header-icon">
				<svg width="26" height="25" viewBox="0 0 26 25" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M11.9441 20.5818C12.3228 20.8497 12.7752 20.9936 13.2391 20.9937L11.9441 20.5818ZM11.9441 20.5818C11.6044 20.3416 11.3391 20.0122 11.1764 19.6313M11.9441 20.5818L11.1764 19.6313M11.1764 19.6313H15.3018C15.1392 20.0122 14.8738 20.3416 14.5341 20.5818C14.1554 20.8497 13.703 20.9936 13.2391 20.9937L11.1764 19.6313ZM5.42653 19.6313H5.4266H9.33118C9.5281 20.5037 10.0116 21.2861 10.7057 21.8526C11.4209 22.4365 12.3158 22.7554 13.2391 22.7554C14.1624 22.7554 15.0573 22.4365 15.7725 21.8526C16.4666 21.2861 16.9501 20.5037 17.147 19.6313H21.0516H21.0517C21.344 19.6309 21.631 19.5534 21.8838 19.4068C22.1366 19.2601 22.3462 19.0494 22.4916 18.7959C22.637 18.5424 22.713 18.255 22.712 17.9628C22.7109 17.6705 22.6329 17.3837 22.4856 17.1313C21.9553 16.2176 21.1516 13.5951 21.1516 10.1562C21.1516 8.05772 20.318 6.04515 18.8341 4.56127C17.3502 3.07739 15.3376 2.24375 13.2391 2.24375C11.1406 2.24375 9.128 3.07739 7.64412 4.56127C6.16024 6.04515 5.3266 8.05772 5.3266 10.1562C5.3266 13.5963 4.52185 16.2179 3.99149 17.1314L4.07797 17.1816L3.99158 17.1313C3.84432 17.3838 3.76625 17.6707 3.76524 17.963C3.76424 18.2554 3.84034 18.5428 3.98587 18.7964C4.1314 19.0499 4.34121 19.2606 4.59414 19.4072C4.84708 19.5537 5.13419 19.631 5.42653 19.6313ZM5.59668 17.8687C6.33498 16.4852 7.0891 13.5615 7.0891 10.1562C7.0891 8.52517 7.73705 6.96089 8.8904 5.80754C10.0437 4.65419 11.608 4.00625 13.2391 4.00625C14.8702 4.00625 16.4345 4.65419 17.5878 5.80754C18.7412 6.96089 19.3891 8.52517 19.3891 10.1562C19.3891 13.5589 20.1415 16.4827 20.8815 17.8687H5.59668Z" fill="#3858E9" stroke="#3858E9" stroke-width="0.2"/>
				</svg>
			</div>
			<div class="athemes-addons-notifications-sidebar-header-content">
				<h3>
					<?php
					if ( ! empty( $aafe_notification_read ) ) {
						esc_html_e( 'Latest News', 'athemes-addons-for-elementor-lite' );
					} else {
						esc_html_e( 'New Update', 'athemes-addons-for-elementor-lite' );
					}
					?>
				</h3>
				<p><?php echo esc_html__( 'Check the latest news from aThemes Addons for Elementor', 'athemes-addons-for-elementor-lite' ); ?></p>
			</div>
		</div>
		<?php if ( $aafe_notification_tabs ) : ?>
			<div class="athemes-addons-notifications-sidebar-tabs">
				<nav class="athemes-addons-tabs-nav athemes-addons-tabs-nav-no-negative-margin" data-tab-wrapper-id="notifications-sidebar">
					<ul>
						<li class="athemes-addons-tabs-nav-item active">
							<a href="#" class="athemes-addons-tabs-nav-link" data-tab-to="notifications-sidebar-aafe">
								<?php echo esc_html__( 'aThemes Addons', 'athemes-addons-for-elementor-lite' ); ?>
							</a>
						</li>
						<li class="athemes-addons-tabs-nav-item">
							<a href="#" class="athemes-addons-tabs-nav-link" data-tab-to="notifications-sidebar-athemes-addons-pro">
								<?php echo esc_html__( 'aThemes Addons Pro', 'athemes-addons-for-elementor-lite' ); ?>
							</a>
						</li>
					</ul>
				</nav>
			</div>
		<?php endif; ?>
		<div class="athemes-addons-notifications-sidebar-body athemes-addons-tab-content-wrapper" data-tab-wrapper-id="notifications-sidebar">
			<div class="athemes-addons-tab-content active" data-tab-content-id="notifications-sidebar-aafe">
				<?php if ( ! empty( $aafe_notifications ) ) : ?>
					<?php $aafe_display_version = false; ?>
					<?php foreach ( $aafe_notifications as $aafe_notification ) : ?>
						<?php $aafe_date = isset( $aafe_notification->post_date ) ? $aafe_notification->post_date : false; ?>
						<?php $aafe_version = isset( $aafe_notification->post_title ) ? $aafe_notification->post_title : false; ?>
						<?php $aafe_content = isset( $aafe_notification->post_content ) ? $aafe_notification->post_content : false; ?>
						<div class="athemes-addons-notification">
							<?php if ( $aafe_date ) : ?>
								<span class="athemes-addons-notification-date" data-raw-date="<?php echo esc_attr( $aafe_date ); ?>">
									<?php echo esc_html( date_format( date_create( $aafe_date ), 'F j, Y' ) ); ?>
									<?php if ( $aafe_display_version ) : ?>
										<span class="athemes-addons-notification-version">(<?php echo esc_html( $aafe_version ); ?>)</span>
									<?php endif; ?>
								</span>
							<?php endif; ?>
							<?php if ( $aafe_content ) : ?>
								<div class="athemes-addons-notification-content">
									<?php echo wp_kses_post( $aafe_content ); ?>
								</div>
							<?php endif; ?>
						</div>
						<?php $aafe_display_version = true; ?>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="athemes-addons-notification">
						<div class="athemes-addons-notification-content">
							<p class="changelog-description"><?php echo esc_html__( 'No notifications found', 'athemes-addons-for-elementor-lite' ); ?></p>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $aafe_notification_tabs ) : ?>
			<div class="athemes-addons-tab-content" data-tab-content-id="notifications-sidebar-athemes-addons-pro">
				<?php if ( ! empty( $aafe_notifications_pro ) ) : ?>
					<?php $aafe_display_version = false; ?>
					<?php foreach ( $aafe_notifications_pro as $aafe_notification ) : ?>
						<?php $aafe_date = isset( $aafe_notification->post_date ) ? $aafe_notification->post_date : false; ?>
						<?php $aafe_version = isset( $aafe_notification->post_title ) ? $aafe_notification->post_title : false; ?>
						<?php $aafe_content = isset( $aafe_notification->post_content ) ? $aafe_notification->post_content : false; ?>
						<div class="athemes-addons-notification">
							<?php if ( $aafe_date ) : ?>
								<span class="athemes-addons-notification-date" data-raw-date="<?php echo esc_attr( $aafe_date ); ?>">
									<?php echo esc_html( date_format( date_create( $aafe_date ), 'F j, Y' ) ); ?>
									<?php if ( $aafe_display_version ) : ?>
										<span class="athemes-addons-notification-version">(<?php echo esc_html( $aafe_version ); ?>)</span>
									<?php endif; ?>
								</span>
							<?php endif; ?>
							<?php if ( $aafe_content ) : ?>
								<div class="athemes-addons-notification-content">
									<?php echo wp_kses_post( $aafe_content ); ?>
								</div>
							<?php endif; ?>
						</div>
						<?php $aafe_display_version = true; ?>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="athemes-addons-notification">
						<div class="athemes-addons-notification-content">
							<p class="changelog-description"><?php echo esc_html__( 'No notifications found', 'athemes-addons-for-elementor-lite' ); ?></p>
						</div>
					</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="wrap athemes-addons-wrap">
	<?php 
	$aafe_widgets = athemes_addons_get_translated_widgets();

	$aafe_active_modules = 0;
	$aafe_option_modules = get_option( 'athemes-addons-modules', array() );

	if ( ! empty( $aafe_widgets ) ) {
		foreach ( $aafe_widgets as $aafe_data ) {
			if ( ! empty( $aafe_data['modules'] ) ) {
				foreach ( $aafe_data['modules'] as $aafe_module_id => $aafe_module ) {
					if ( ! empty( $aafe_option_modules[ $aafe_module_id ] ) ) {
						$aafe_active_modules++;
					}
				}
			}
		}
	}

	?>

	<div class="athemes-addons-modules-header">

		<div class="athemes-addons-modules-header-left">

			<div class="athemes-addons-modules-header-heading"><?php esc_html_e( 'Welcome To aThemes Addons for Elementor', 'athemes-addons-for-elementor-lite' ); ?> <?php esc_html_e('👋🏻', 'athemes-addons-for-elementor-lite'); ?></div>
			
			<div class="athemes-addons-modules-header-subheading"><?php esc_html_e( 'We’re glad to see you :)', 'athemes-addons-for-elementor-lite' ); ?></div>
		</div>

		<div class="athemes-addons-modules-header-right">

			<ul class="athemes-addons-modules-header-shortlinks">

				<li class="athemes-addons-modules-header-shortlinks-get-help">
					<a href="<?php echo esc_url( athemes_addons_admin_upgrade_link( 'https://athemes.com/support/', array(), 'dashboard-support-link' ) ); ?>" target="_blank">

						<span><?php esc_html_e( 'Get', 'athemes-addons-for-elementor-lite' ); ?> <strong><?php esc_html_e( 'help and support', 'athemes-addons-for-elementor-lite' ); ?></strong></span>

						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M10.8626 8.04102L14.2809 12.0291L10.8626 16.0172L9.72363 15.041L12.3053 12.0291L9.72363 9.01721L10.8626 8.04102Z" fill="#757575"/>
						</svg>

					</a>
				</li>

				<li class="athemes-addons-modules-header-shortlinks-leave-review">
					<a href="https://wordpress.org/support/plugin/athemes-addons-for-elementor-lite/reviews/?filter=5#new-post">

						<span><?php esc_html_e( 'Leave a', 'athemes-addons-for-elementor-lite' ); ?> <strong><?php esc_html_e( 'review', 'athemes-addons-for-elementor-lite' ); ?></strong></span>

						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M10.8626 8.04102L14.2809 12.0291L10.8626 16.0172L9.72363 15.041L12.3053 12.0291L9.72363 9.01721L10.8626 8.04102Z" fill="#757575"/>
						</svg>

					</a>
				</li>

				<li class="athemes-addons-modules-header-shortlinks-feedback">
					<a href="<?php echo esc_url( athemes_addons_admin_upgrade_link( 'https://athemes.com/feature-request/', array(), 'dashboard-feature-request-link' ) ); ?>" target="_blank">

						<span><?php esc_html_e( 'Have an', 'athemes-addons-for-elementor-lite' ); ?> <strong><?php esc_html_e( 'idea or feedback?', 'athemes-addons-for-elementor-lite' ); ?></strong></span>

						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M10.8626 8.04102L14.2809 12.0291L10.8626 16.0172L9.72363 15.041L12.3053 12.0291L9.72363 9.01721L10.8626 8.04102Z" fill="#757575"/>
						</svg>

					</a>
				</li>

			</ul>

		</div>

	</div>

	<div class="athemes-addons-modules-panel">		
		<div class="athemes-addons-dashboard-tabs-nav">
		<?php foreach ( $aafe_dashboard_tabs as $aafe_tab_id => $aafe_tab ) : ?>
			<a href="<?php echo esc_html( $aafe_tab['link'] ); ?>" data-tab="<?php echo esc_attr( $aafe_tab_id ); ?>" class="athemes-addons-tab-nav-item <?php echo ( 'widgets' === $aafe_tab_id ) ? 'active' : ''; ?>">
				<?php echo esc_html( $aafe_tab['title'] ); ?>
			</a>
		<?php endforeach; ?>
		</div>

		<?php foreach ( $aafe_dashboard_tabs as $aafe_tab_id => $aafe_tab ) : ?>
			<div id="<?php echo esc_attr( $aafe_tab_id ); ?>" class="athemes-addons-dashboard-tab-page <?php echo ( ( isset( $_GET['section'] ) && $_GET['section'] === $aafe_tab_id ) || !isset( $_GET['section'] ) && 'widgets' === $aafe_tab_id ) ? 'active' : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended  ?>">
				<?php require_once ATHEMES_AFE_DIR . 'admin/pages/page-' . $aafe_tab_id . '.php'; ?>
			</div>
		<?php endforeach; ?>
	</div>

</div>