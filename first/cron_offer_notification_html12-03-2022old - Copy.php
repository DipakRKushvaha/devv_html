<?php
class cron_offer_notification_html extends model {
	
	/* offer notification guest send mail html content manage */
	public static function _manage_offer_mail_content($tmpMData = array()){
		extract($tmpMData);
		$arrGuestHTML = $tmpRCount = array();
		$tmpIGCount = array_count_values(array_column($arrInquiry,'user_uid'));
		foreach($arrInquiry as $key => $inquiryV){
			
			/* $shortlist_url = config::site_url().'en/guest/shortlist/'.$inquiryV['inq_id']; */
			$shortlist_url = config::site_url().'en/guest/shortlist/'.$inquiryV['inq_id']."?view_shortlist=".md5($inquiryV['uid'].$inquiryV['inq_id']);
			
			/* get all inquiry offer */
			$inquiryOffer = $arrOffer[$inquiryV['uid']];
			
			if(isset($inquiryOffer) && is_array($inquiryOffer) && count($inquiryOffer)){
				
				$arrMData = array(
					'arrProperty'	=> $arrProperty,
					'arrInquiry' 	=> $arrInquiry,
					'arrOffer' 		=> $arrOffer,
					'mail_slug' 	=> $tdV['m_slug'],
				);
				
				/* mail send html generate and send mail */
				_manage_offer_mail_content($arrMData);
						
						
				$requestHTML = $offerHTML = '';
				$tmp_req_count = '';
				
				$u_uid = $inquiryV['user_uid'];
				
				if(isset($tmpIGCount[$u_uid]) && $tmpIGCount[$u_uid] > 1){
					if(isset($tmpRCount[$u_uid])){					
						$tmp_req_count = $tmpRCount[$u_uid];
					}else{
						$tmp_req_count = 1;
					}
					$tmpRCount[$u_uid] = $tmp_req_count;
				}
			
				$tmpTblHeading = array(
					'[REQ_COUNT]'		=> $tmp_req_count,
					'[BOOKING_LINK]'	=> $shortlist_url,
					'[BOOKING_ID]'		=> $inquiryV['inq_id'],
					'[CHECK_IN]'		=> date('D d M',strtotime($inquiryV['check_in'])),
					'[CHECK_OUT]'		=> date('D d M',strtotime($inquiryV['check_out'])),
					'[TOTAL_NIGHT]'		=> $inquiryV['total_night'],
					'[NO_OF_PEOPLE]'	=> $inquiryV['no_of_people'],
					'[LOCATION_TXT]'	=> $inquiryV['google_address'],
				);
				
				$requestHTML .= cron_offer_notification_html::_request_information_txt($tmpTblHeading);
				if(!empty($tmp_req_count)){
					if(isset($tmpRCount[$u_uid])){
						$tmpRCount[$u_uid]++;
					}
				}
				
				foreach($inquiryOffer as $ioK => $offer){					
					
					$tmpP = $arrProperty[$offer['property_uid']];					
					$property_img = $tmpP['image'] ? PROPERTY_IMG.$tmpP['image'] : 'images/photo5.png';
					/* $property_url = config::site_url().'en/accommodation/'.$arrSlug[$tmpP['city_uid']].'/'.$tmpP['slug']; */
					
					$nightly_budget = $offer['total_budget'];
					if($offer['total_budget'] > 0){
						if($offer['budget_type'] == 'Weekly'){
							$nightly_budget = $offer['total_budget'] / count(explode(',',$inquiryV['nights_needed']));
						}else if($offer['budget_type'] == 'Monthly'){
							$nightly_budget = $offer['total_budget'] / 30;
						}else{
							$nightly_budget = $offer['total_budget'] / $inquiryV['total_night'];
						}
					}
					
					$tmpTblOffer = array(
						'[PROPERTY_IMG]'		=> config::site_url().$property_img,
						'[PROPERTY_TITLE]'		=> $tmpP['title'],
						'[TOTAL_BUDGET]'		=> _CSymbol.number_format($offer['total_budget'],2),
						'[PER_NIGHT_BUDGET]'	=> _CSymbol.number_format($nightly_budget,2),
						'[POST_CODE]'			=> $tmpP['postcode'],
						'[PROPERTY_TYPE]'		=> $tmpP['property_type'],
						'[CLEANING_FREQUENCY]'	=> $offer['cleaning_frequency'],
						'[BED]'					=> $tmpP['sleep'],
						'[BEDROOMS]'			=> $tmpP['bedroom'],
						'[NO_OF_GUEST]'			=> $offer['no_of_separate_guest'],
						'[PARKING]'				=> $tmpP['parking'],
						'[BOOK_NOW_BTN]'		=> $shortlist_url,
					);
					
					$offerHTML .= cron_offer_notification_html::_request_offer_txt($tmpTblOffer);							
					$offerHTML .= cron_offer_notification_html::_divider_txt();
				}
				$requestHTML .= $offerHTML;
				
				$tmpTblReviewS = array( '[REVIEW_SHORTLIST_URL]' => $shortlist_url, );			
				$requestHTML .= cron_offer_notification_html::_review_shortlist_btn_txt($tmpTblReviewS);
				
				$arrGuestHTML[$u_uid]['content'][] = $requestHTML;	
			}
		}
		/* print_r($arrGuestHTML); die; */
		/* send email to guest with request html */
		if(count($arrGuestHTML)){
			$ex_gw = " WHERE uid IN ('".implode("','",array_keys($arrGuestHTML))."')";
			$tmpGuest = users::_get_rows(array('uid','email'),array(),$ex_gw);
			$arrGEmail = array_column($tmpGuest,'email','uid');
			foreach($arrGuestHTML as $g_uid => $mailContent){
				
				/* send email */
				$tmpData = array(
					'[REQUEST_OFFER_CONTENT]' 		=> implode('<br>',$mailContent['content']),
				);
				
				$tmp_slug = $mail_slug;
				$to = $arrGEmail[$g_uid];
				/* $to = "guest@comfyworkers.com"; */
				email_template::email($tmp_slug,$to,$tmpData);
			}
		}
	}
	
	/* get request offer html */
	public static function _request_offer_txt($tmpData = array()){
		ob_start(); ?>
		<table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnImageCardBlock">
			<tbody class="mcnImageCardBlockOuter">
				<tr>
					<td class="mcnImageCardBlockInner" valign="top" style="padding-top:15px; padding-right:18px; padding-bottom:9px; padding-left:18px;">
						<table border="0" cellpadding="0" cellspacing="0" class="mcnImageCardRightContentOuter" width="100%">
							<tbody>
								<tr>
									<td align="center" valign="top" class="mcnImageCardRightContentInner" style="padding:0;">
										<table align="left" border="0" cellpadding="0" cellspacing="0" class="mcnImageCardRightImageContentContainer" width="130">
											<tbody>
												<tr>
													<td class="mcnImageCardRightImageContentE2E " align="center" valign="top" style="padding-top:0px; padding-right:0; padding-bottom:0px; padding-left:0px;">
														<div class="cnt_left" style="display: inline-block;vertical-align: top;width: 100%;">
															<div class="cnt_image">
																<img src="[PROPERTY_IMG]" width="130" height="125" alt="Image" style="width: 130px;height: 125px;object-fit: cover;border-radius: 6px;max-width: 130px;max-height: 125px;">
															</div>
														</div>
													</td>
												</tr>
											</tbody>
										</table>
										<table class="mcnImageCardRightTextContentContainer" align="right" border="0" cellpadding="0" cellspacing="0" width="410">
											<tbody>
												<tr>
													<td valign="top" class="mcnTextContent" style="text-align: left;">
														<div class="cnt_right" style="display: inline-block;width: 100%;vertical-align: top;">
															<h2 style="margin: 0;color: #111;font-size: 18px;font-weight: 600;margin-top: -8px;margin-bottom: 12px;">[PROPERTY_TITLE]</h2>
														</div>
														<font>
														<table border="0" cellpadding="0" cellspacing="0" width="100%" class="property_list">

															<tbody><tr>

																<td style="padding-bottom: 5px;">

																	<div class="icn_prop">

																		  <img src="https://www.comfyworkers.com/images/icon17.png" alt="" width="21" height="21" style="width: 21px;height: 21px;object-fit: contain;vertical-align: middle;margin-right: 10px;float: left;margin-bottom: 10px;max-width: 21px;max-height: 21px;object-fit: contain;">

												  <span style="margin-left: 0;display: inline-block;vertical-align: top;width: 80%;font-size: 14px;font-weight: 400;color: #606C7E;margin-bottom: 7px;align-items: center;line-height: 21px;max-width: 80%;">[POST_CODE]</span>

																	</div>

																</td>

																<td>

																	<div class="icn_prop">

																		<img src="https://www.comfyworkers.com/images/icon18.png" alt="" width="21" height="21" style="width: 21px;height: 21px;object-fit: contain;vertical-align: middle;margin-right: 10px;float: left;margin-bottom: 10px;max-width: 21px;max-height: 21px;object-fit: contain;">

																			<span style="margin-left: 0;display: inline-block;vertical-align: top;width: 80%;font-size: 14px;font-weight: 400;color: #606C7E;margin-bottom: 7px;align-items: center;line-height: 21px;max-width: 80%;">[PROPERTY_TYPE]</span>

																	</div>

																</td>

															</tr>

															<tr>

																<td style="padding-bottom: 5px;">

																	<div class="icn_prop">

																		<img src="https://www.comfyworkers.com/images/icon20.png" alt="" width="21" height="21" style="width: 21px;height: 21px;object-fit: contain;vertical-align: middle;margin-right: 10px;float: left;margin-bottom: 10px;max-width: 21px;max-height: 21px;object-fit: contain;">

																			<span style="margin-left: 0;display: inline-block;vertical-align: top;width: 80%;font-size: 14px;font-weight: 400;color: #606C7E;margin-bottom: 7px;align-items: center;line-height: 21px;max-width: 80%;">[PARKING]</span>

																	</div>

																</td>

																<td>

																	<div class="icn_prop">

																		<img src="https://www.comfyworkers.com/images/icon18.png" alt="" width="21" height="21" style="width: 21px;height: 21px;object-fit: contain;vertical-align: middle;margin-right: 10px;float: left;margin-bottom: 10px;max-width: 21px;max-height: 21px;object-fit: contain;">

																			<span style="margin-left: 0;display: inline-block;vertical-align: top;width: 80%;font-size: 14px;font-weight: 400;color: #606C7E;margin-bottom: 7px;align-items: center;line-height: 21px;max-width: 80%;">[CLEANING_FREQUENCY]</span>

																	</div>

																</td>

															</tr>

															<tr>

																<td style="padding-bottom: 5px;">

																	<div class="icn_prop">

																		<img src="https://www.comfyworkers.com/images/icon19.png" alt="" width="21" height="21" style="width: 21px;height: 21px;object-fit: contain;vertical-align: middle;margin-right: 10px;float: left;margin-bottom: 10px;max-width: 21px;max-height: 21px;object-fit: contain;">

																			<span style="margin-left: 0;display: inline-block;vertical-align: top;width: 80%;font-size: 14px;font-weight: 400;color: #606C7E;margin-bottom: 7px;align-items: center;line-height: 16px;max-width: 80%;">[BEDROOMS] Bedrooms - <br>[BED] Bed - [NO_OF_GUEST] Guests</span>

																	</div>

																</td>

															</tr>

														</tbody></table> 
													</font>

													</td>

												</tr>

											</tbody>

										</table>
									</td>
								</tr>
								<tr>
									<td class="budget_price">
										<table align="left" border="0" cellpadding="0" cellspacing="0" class="proper_price_wrap proper_price_left" width="400">
											<tbody>
												<tr>
													<td>
														<label>
															<div class="cnt_img_bot" style="display: inline-block;width: 100%;vertical-align: top;">
																<div class="cnt_img_left" style="display: inline-block;vertical-align: top;">
																		<b style="font-size: 15px;">Budget:</b>
																</div>
																<div class="cnt_img_right" style="margin-left: 5px;display: inline-block;vertical-align: top;">
																	<b style="color: #5A1087;font-size: 15px;">[TOTAL_BUDGET]</b>
																	<br>
																	<span style="display: block;font-size: 13px;color: #606C7E;">[PER_NIGHT_BUDGET] per night</span>
																</div>
															</div>
														</label>
													</td>
												</tr>
											</tbody>
										</table>

										<table align="center" border="0" cellpadding="0" cellspacing="0" class="proper_price_wrap proper_price_right" width="140">

											<tbody>

												<tr>

													<td align="center" style="text-align: center;background-color: #5A1087;border-radius: 6px;padding: 10px 15px;">

														<a href="[BOOK_NOW_BTN]" style="background-color: #5A1087;font-size: 18px;text-transform: uppercase;font-weight: 800;color: #fff;text-decoration: none;border-radius: 6px;display: inline-block;width: 100%;text-align: center;max-width: 100%;">Book now</a>

													</td>

												</tr>

											</tbody>

										</table>

									</td>

								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
		$tmpHtml = ob_get_clean();
		$tmpHtml = str_replace(array_keys($tmpData),array_values($tmpData),$tmpHtml);
		return $tmpHtml;
	}

	/* get request information html */
	public static function _request_information_txt($tmpData = array()){
		ob_start(); ?>
		<table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="min-width:100%;">
			<tbody class="mcnTextBlockOuter">
				<tr>
					<td valign="top" class="mcnTextBlockInner" style="padding-top:9px;">
						<table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width:100%; min-width:100%;" width="100%" class="mcnTextContentContainer">
							<tbody>
								<tr>
									<td valign="top" class="mcnTextContent" style="padding: 0px 20px;">
										<div class="box_dsgn" style="border-radius: 6px;">
											<table border="0" cellpadding="0" cellspacing="0" width="100%">
												<tbody>
													<tr>
														<td style="background-color: #F1F3FA;padding: 15px 30px;border-radius: 6px;">
															<span style="color: #606C7E;font-size: 14px;">Booking Request [REQ_COUNT] <a style="text-decoration: none;" href="[BOOKING_LINK]"><small style="color: #ff950b;font-size: 15px;">#[BOOKING_ID]</small><a/></span>
															<br>
															<span class="box_desc" style="line-height: 24px;margin-top: 8px;display: block;border-collapse: collapse;text-indent: unset;border-spacing: 0;font-size: 16px;color: #606C7E;font-weight: 500;">On
																<b style="color: #000;font-weight: 500;">[CHECK_IN] - [CHECK_OUT] ([TOTAL_NIGHT] Nights)</b> for 
																<b style="color: #000;font-weight: 500;">[NO_OF_PEOPLE] Peoples</b> in <br> at <b style="color: #000;font-weight: 500;">[LOCATION_TXT]</b> Is ready for your review.
															</span>
														</td>
													</tr>
												</tbody>
											</table>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
		$tmpHtml = ob_get_clean();
		$tmpHtml = str_replace(array_keys($tmpData),array_values($tmpData),$tmpHtml);
		return $tmpHtml;
	}

	/* get offer divider html */
	public static function _divider_txt($tmpData = array()){
		ob_start(); ?>
		<table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnDividerBlock" style="min-width:100%;">
			<tbody class="mcnDividerBlockOuter">
				<tr>
					<td class="mcnDividerBlockInner" style="min-width:100%; padding:14px;">
						<table class="mcnDividerContent" border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width: 100%;border-top: 2px solid #EAEAEA;">
							<tbody>
								<tr>
									<td>
										<span></span>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
		$tmpHtml = ob_get_clean();
		$tmpHtml = str_replace(array_keys($tmpData),array_values($tmpData),$tmpHtml);
		return $tmpHtml;
	}

	/* get shortlist button html */
	public static function _review_shortlist_btn_txt($tmpData = array()){
		ob_start(); ?>
		<table border="0" cellpadding="0" cellspacing="0" width="100%" class="property_last_wrap" style="min-width:100%;">
			<tbody class="mcnTextBlockOuter">
				<tr>
					<td valign="top" class="mcnTextBlockInner">
						<?php /*
						<table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width:100%; min-width:100%;" width="100%" class="mcnTextContentContainer">
							<tbody>
								<tr>
									<td valign="top" class="mcnTextContent" style="padding-top:0; padding-right:18px; padding-bottom:9px; padding-left:18px;">
										<span style="font-size: 16px;font-weight: 600;color: #606C7E;">+5 More Properties</span>
									</td>
								</tr>
							</tbody>
						</table> */
						?>
						<table align="center" border="0" cellpadding="0" cellspacing="0" width="220" class="mcnTextContentContainer rvw_btn_tble" style="margin-top: 40px;">
							<tbody>
								<tr>
									<td valign="top" class="mcnTextContent" style="padding-top:12px; padding-right:10px; padding-bottom:12px; padding-left:10px;background-color: #ff960c;border-radius: 6px;height: auto;max-height: unset;line-height: normal;">
										<div class="rvw_btn" style="text-align: center;height: auto;max-height: unset;">
											<a href="[REVIEW_SHORTLIST_URL]" style="background-color: #ff960c;text-decoration: none;border-radius: 10px;font-size: 17px;color: #fff;font-weight: 700;display: inline-block;text-transform: uppercase;height: auto;max-height: unset;">Review Shortlist</a>
										</div>
									</td>
								</tr>
							</tbody>
						</table>									
					</td>
				</tr>
			</tbody>
		</table>
		<?php
		$tmpHtml = ob_get_clean();
		$tmpHtml = str_replace(array_keys($tmpData),array_values($tmpData),$tmpHtml);
		return $tmpHtml;
	}
}
?>