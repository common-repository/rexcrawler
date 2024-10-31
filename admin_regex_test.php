<?php
function rc_regex_test(){
	if(isset($_POST['test_regex'])){
		$regex = stripcslashes($_POST['regex']);
		$url = stripcslashes($_POST['url']);
	}
	
	// Echo header etc
	echo '<div class="wrap">
			<div id="icon-tools" class="icon32">
				<br />
			</div>
			<h2>'.__('rexCrawler search-pattern tester', 'rexCrawler').'</h2>';
	echo '<p>'.__('On this page you&apos;re able to test your search-patterns, before they&apos;re implemented.', 'rexCrawler').'</p>';
	
	// Echo the form
	echo "<form method='post' action='?page=rc_regex_test'>
			<div id='poststuff' class='metabox-holder'>
				<div class='stuffbox'><h3>".__('Parameters', 'rexCrawler')."</h3>
					<div class='inside'>
						<table class='form-table'>
							<tbody>
								<tr>
									<td class='first'><label for='regex'>".__('Search-pattern:', 'rexCrawler')."</label></td>
									<td><input type='text' id='regex' name='regex' maxlength='100' value='$regex' style='width: 100%;' /></td>
								</tr>
								<tr>
									<td class='first'><label for='url'>".__('URL:', 'rexCrawler')."</label></td>
									<td><input type='text' id='url' name='url' maxlength='100' value='$url' style='width: 100%;' /></td>
								</tr>
								<tr>
									<td colspan='2'><input type='submit' class='button-primary' name='test_regex' value='".__('Test search-pattern', 'rexCrawler')."' /></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</form>

		<div id='poststuff' class='metabox-holder'>
			<div class='stuffbox'>
				<h3>".__('Search-pattern reference guide', 'rexCrawler')."</h3>
				<div class='inside' style='margin-left: 60px;'>
					<div>
						<p>".__('A search-pattern always starts with &apos;&sol;&apos; and ends with &apos;&sol;i&apos;. &apos;&sol;i&apos; makes the pattern case-insensitive.', 'rexCrawler')."</p>
						<p>".__('Below is a reference guide to regular expressions &lpar;RegEx&rpar; based on Ruby RegEx.', 'rexCrawler')."</p>
					</div>
					<div id='quickref'> 
						<div style='float:left'> 
							<table> 
								<tr> 
									<td><code>[abc]</code></td> 
									<td>A single character: a, b or c</td> 
								</tr> 
								<tr> 
									<td><code>[^abc]</code></td> 
									<td>Any single character <em>but</em> a, b, or c</td> 
								</tr> 
								<tr> 
									<td><code>[a-z]</code></td> 
									<td>Any single character in the range a-z</td> 
								</tr> 
								<tr> 
									<td><code>[a-zA-Z]</code></td> 
									<td>Any single character in the range a-z or A-Z</td> 
								</tr> 
								<tr> 
									<td><code>^</code></td> 
									<td>Start of line</td> 
								</tr> 
								<tr> 
									<td><code>$</code></td> 
									<td>End of line</td> 
								</tr> 
								<tr> 
									<td><code>\A</code></td> 
									<td>Start of string</td> 
								</tr> 
								<tr> 
									<td><code>\z</code></td> 
									<td>End of string</td> 
								</tr> 
							</table> 
						</div> 
						<div style='float:left'> 
							<table> 
								<tr> 
									<td><code>.</code></td> 
									<td>Any single character</td> 
								</tr> 
								<tr> 
									<td><code>\s</code></td> 
									<td>Any whitespace character</td> 
								</tr> 
								<tr> 
									<td><code>\S</code></td> 
									<td>Any non-whitespace character</td> 
								</tr> 
								<tr> 
									<td><code>\d</code></td> 
									<td>Any digit</td> 
								</tr> 
								<tr> 
									<td><code>\D</code></td> 
									<td>Any non-digit</td> 
								</tr> 
								<tr> 
									<td><code>\w</code></td> 
									<td>Any word character (letter, number, underscore)</td> 
								</tr> 
								<tr> 
									<td><code>\W</code></td> 
									<td>Any non-word character</td> 
								</tr> 
								<tr> 
									<td><code>\b</code></td> 
									<td>Any word boundary character</td> 
								</tr> 
							</table> 
						</div> 
						<table> 
							<tr> 
								<td><code>(...)</code></td> 
								<td>Capture everything enclosed</td> 
							</tr> 
							<tr> 
								<td><code>(a|b)</code></td> 
								<td>a or b</td> 
							</tr> 
							<tr> 
								<td><code>a?</code></td> 
								<td>Zero or one of a</td> 
							</tr> 
							<tr> 
								<td><code>a*</code></td> 
								<td>Zero or more of a</td> 
							</tr> 
							<tr> 
								<td><code>a+</code></td> 
								<td>One or more of a</td> 
							</tr> 
							<tr> 
								<td><code>a{3}</code></td> 
								<td>Exactly 3 of a</td> 
							</tr> 
							<tr> 
								<td><code>a{3,}</code></td> 
								<td>3 or more of a</td> 
							</tr> 
							<tr> 
								<td><code>a{3,6}</code></td> 
								<td>Between 3 and 6 of a</td> 
							</tr>        
						</table>
						<div style='text-align: right; margin-top: 10px;'>
							".__('Picked from <em><a href="http://rubular.com/regexes/" target="_BLANK">Rublar - a Ruby regular expression editor</a></em>', 'rexCrawler')."
						</div>
					</div>
				</div>
			</div>
		</div>";
			
		if(isset($_POST['test_regex'])){
			echo '<div id="poststuff" class="metabox-holder">
					<div class="stuffbox">
						<h3>'.__('Result', 'rexCrawler').'</h3>
						<div class="inside">';
			
							$result = file_get_contents($url);
							
							if(!empty($result)){
								preg_match_all($regex, $result, $data);
								
								$data = !empty($data[1]) ? array_map("trim", $data[1]) : FALSE;
								
								$output = '<pre>';
								$output .= print_r(str_replace(array(0 => 'æ', 1 => 'ø', 2 => 'å', 3 => 'Æ', 4 => 'Ø', 5 => 'Å'),
															   array(0 => '&aelig;', 1 => '&oslash;', 2 => '&aring;', 3 => '&Aelig;', 4 => '&Oslash;', 5 => '&Aring;'),
															   $data), true);
								$output .= '</pre>';
								
								echo $output;
							}else{
								echo '<p class="italic">'.__('No data found using the given regular expression.', 'rexCrawler').'</p>';
							}
			
			echo "		</div>
					</div>
				</div>";
		}
	
	echo '</div>';
}
?>