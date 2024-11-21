<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\WooCommerce_Actions\Lib;

class E164 {

	public static function from_phone_number( string $phone_number ): ?string {
		$phone_number = static::pre_process( $phone_number );
		$parsed_phone_number = static::parse_phone_number( $phone_number );
		return static::from_parsed_phone_number( $parsed_phone_number );
	}

	private static function pre_process( string $phone_number ): string {
		return trim( $phone_number );
	}

	private static function post_process( string $phone_number ): string {
		return preg_replace( '/[^0-9\+]/', '', $phone_number );
	}

	public static function verify_e164_format( string $phone_number ) {
		if ( static::looks_like_e164_format( $phone_number ) ) {
			return true;
		}
	}

	public static function looks_like_e164_format( string $phone_number ): bool {
		return preg_match( '^\+[1-9]\d{1,14}$', $phone_number ) !== false;
	}

	public static function parse_phone_number( string $phone_number ): ?array {
		$country_code = static::get_country_code( $phone_number );
		if ( empty( $country_code ) ) {
			return null;
		}
		return static::parse_as_country_code( $country_code, $phone_number );
	}

	public static function from_parsed_phone_number( array $parsed_phone_number ) {
		return implode(
			[
				'+',
				$parsed_phone_number['country_code'],
				$parsed_phone_number['subscriber_number'],
			]
		);
	}

	public static function get_country_code( string $phone_number ): string {
		$offset = static::looks_like_e164_format( $phone_number ) ? 1 : 0;
		for( $length = 1; $length < 3; $length++ ) {
			$possible_country_code = substr( $phone_number, $offset, $length );
			$possible_match = static::get_country_config( $possible_country_code );
			if ( ! empty( $possible_match ) ) {
				if ( static::verify_country_code_and_subscriber_length( $possible_country_code, $phone_number ) ) {
					return $possible_country_code;
				}
			}
		}
	}

	public static function parse_as_country_code( string $country_code, string $phone_number ): ?array {
		$country_config = static::get_country_config( $country_code );
		if ( empty( $country_config ) ) {
			return false;
		}
		
		$phone_number = static::post_process( $phone_number );
		$offset = static::looks_like_e164_format( $phone_number ) ? 1 : 0;
		$country_code_length = strlen( $country_code );
		$phone_number_length = strlen( $phone_number );
		$international_prefix_length = $offset + $country_code_length;
		$subscriber_length = $phone_number_length - $international_prefix_length;
		$subscriber_number = substr( $phone_number, $international_prefix_length );

		if ( $subscriber_length >= $country_config['min_subscriber_length'] && $subscriber_length <= $country_config['max_subscriber_length'] ) {
			return [
				'country_code' => $country_code,
				'subscriber_number' => $subscriber_number,
			];
		}
		return null;
	}

	public static function verify_country_code_and_subscriber_length( string $country_code, string $phone_number ): bool {
		return ! empty( static::parse_as_country_code( $country_code, $phone_number ) );
	}

	public static function get_country_config( string $country_code ): ?array {
		return static::COUNTRY_CONFIG[ $country_code ] ?? null;
	}

	public const COUNTRY_CONFIG = array(
		'1'   => array(
			'region'                => 'United States',
			'min_subscriber_length' => 10,
			'max_subscriber_length' => 10,
		),
		'20'  => array(
			'region'                => 'Egypt',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 9,
		),
		'212' => array(
			'region'                => 'Morocco',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'213' => array(
			'region'                => 'Algeria',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 9,
		),
		'216' => array(
			'region'                => 'Tunisia',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'218' => array(
			'region'                => 'Libya',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 9,
		),
		'220' => array(
			'region'                => 'Gambia',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 7,
		),
		'221' => array(
			'region'                => 'Senegal',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'222' => array(
			'region'                => 'Mauritania',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 7,
		),
		'223' => array(
			'region'                => 'Mali',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'224' => array(
			'region'                => 'Guinea',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'225' => array(
			'region'                => 'Ivory Coast',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'226' => array(
			'region'                => 'Burkina Faso',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'227' => array(
			'region'                => 'Niger',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'228' => array(
			'region'                => 'Togo',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'229' => array(
			'region'                => 'Benin',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'230' => array(
			'region'                => 'Mauritius',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 7,
		),
		'231' => array(
			'region'                => 'Liberia',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 8,
		),
		'232' => array(
			'region'                => 'Sierra Leone',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'233' => array(
			'region'                => 'Ghana',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 9,
		),
		'234' => array(
			'region'                => 'Nigeria',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 10,
		),
		'235' => array(
			'region'                => 'Chad',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'236' => array(
			'region'                => 'Central African Republic',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'237' => array(
			'region'                => 'Cameroon',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'238' => array(
			'region'                => 'Cape Verde',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 7,
		),
		'239' => array(
			'region'                => 'Sao Tome and Principe',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 7,
		),
		'240' => array(
			'region'                => 'Equatorial Guinea',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'241' => array(
			'region'                => 'Gabon',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 7,
		),
		'243' => array(
			'region'                => 'DR Congo',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 9,
		),
		'244' => array(
			'region'                => 'Angola',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'248' => array(
			'region'                => 'Seychelles',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 7,
		),
		'249' => array(
			'region'                => 'Sudan',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'250' => array(
			'region'                => 'Rwanda',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'251' => array(
			'region'                => 'Ethiopia',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'252' => array(
			'region'                => 'Somalia',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 8,
		),
		'253' => array(
			'region'                => 'Djibouti',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 6,
		),
		'254' => array(
			'region'                => 'Kenya',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 10,
		),
		'255' => array(
			'region'                => 'Tanzania',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'256' => array(
			'region'                => 'Uganda',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'257' => array(
			'region'                => 'Burundi',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'258' => array(
			'region'                => 'Mozambique',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 9,
		),
		'260' => array(
			'region'                => 'Zambia',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'261' => array(
			'region'                => 'Madagascar',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 10,
		),
		'263' => array(
			'region'                => 'Zimbabwe',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 10,
		),
		'264' => array(
			'region'                => 'Namibia',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 10,
		),
		'265' => array(
			'region'                => 'Malawi',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 8,
		),
		'266' => array(
			'region'                => 'Lesotho',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'267' => array(
			'region'                => 'Botswana',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 8,
		),
		'268' => array(
			'region'                => 'Eswatini',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 8,
		),
		'269' => array(
			'region'                => 'Comoros',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 7,
		),
		'27'  => array(
			'region'                => 'South Africa',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'291' => array(
			'region'                => 'Eritrea',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 7,
		),
		'297' => array(
			'region'                => 'Aruba',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 7,
		),
		'298' => array(
			'region'                => 'Faroe Islands',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 6,
		),
		'299' => array(
			'region'                => 'Greenland',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 6,
		),
		'30'  => array(
			'region'                => 'Greece',
			'min_subscriber_length' => 10,
			'max_subscriber_length' => 10,
		),
		'31'  => array(
			'region'                => 'Netherlands',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'32'  => array(
			'region'                => 'Belgium',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 9,
		),
		'33'  => array(
			'region'                => 'France',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'34'  => array(
			'region'                => 'Spain',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'350' => array(
			'region'                => 'Gibraltar',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'351' => array(
			'region'                => 'Portugal',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 11,
		),
		'352' => array(
			'region'                => 'Luxembourg',
			'min_subscriber_length' => 4,
			'max_subscriber_length' => 11,
		),
		'353' => array(
			'region'                => 'Ireland',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 11,
		),
		'354' => array(
			'region'                => 'Iceland',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 9,
		),
		'355' => array(
			'region'                => 'Albania',
			'min_subscriber_length' => 3,
			'max_subscriber_length' => 9,
		),
		'356' => array(
			'region'                => 'Malta',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'357' => array(
			'region'                => 'Cyprus',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 11,
		),
		'358' => array(
			'region'                => 'Finland',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 12,
		),
		'359' => array(
			'region'                => 'Bulgaria',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 9,
		),
		'36'  => array(
			'region'                => 'Hungary',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 9,
		),
		'370' => array(
			'region'                => 'Lithuania',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'371' => array(
			'region'                => 'Latvia',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 8,
		),
		'372' => array(
			'region'                => 'Estonia',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 10,
		),
		'373' => array(
			'region'                => 'Moldova',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'374' => array(
			'region'                => 'Armenia',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'375' => array(
			'region'                => 'Belarus',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 10,
		),
		'376' => array(
			'region'                => 'Andorra',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 9,
		),
		'377' => array(
			'region'                => 'Monaco',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 9,
		),
		'378' => array(
			'region'                => 'San Marino',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 10,
		),
		'380' => array(
			'region'                => 'Ukraine',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'381' => array(
			'region'                => 'Serbia',
			'min_subscriber_length' => 4,
			'max_subscriber_length' => 12,
		),
		'382' => array(
			'region'                => 'Montenegro',
			'min_subscriber_length' => 4,
			'max_subscriber_length' => 12,
		),
		'385' => array(
			'region'                => 'Croatia',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 12,
		),
		'386' => array(
			'region'                => 'Slovenia',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'387' => array(
			'region'                => 'Bosnia and Herzegovina',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'39'  => array(
			'region'                => 'Italy',
			'min_subscriber_length' => null,
			'max_subscriber_length' => 11,
		),
		'40'  => array(
			'region'                => 'Romania',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'41'  => array(
			'region'                => 'Switzerland',
			'min_subscriber_length' => 4,
			'max_subscriber_length' => 12,
		),
		'420' => array(
			'region'                => 'Czech Republic',
			'min_subscriber_length' => 4,
			'max_subscriber_length' => 12,
		),
		'421' => array(
			'region'                => 'Slovakia',
			'min_subscriber_length' => 4,
			'max_subscriber_length' => 9,
		),
		'423' => array(
			'region'                => 'Liechtenstein',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 9,
		),
		'43'  => array(
			'region'                => 'Austria',
			'min_subscriber_length' => 4,
			'max_subscriber_length' => 13,
		),
		'44'  => array(
			'region'                => 'United Kingdom',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 10,
		),
		'45'  => array(
			'region'                => 'Denmark',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'46'  => array(
			'region'                => 'Sweden',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 13,
		),
		'47'  => array(
			'region'                => 'Norway',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 8,
		),
		'48'  => array(
			'region'                => 'Poland',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 9,
		),
		'49'  => array(
			'region'                => 'Germany',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 13,
		),
		'500' => array(
			'region'                => 'Falkland Islands',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 5,
		),
		'501' => array(
			'region'                => 'Belize',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 7,
		),
		'502' => array(
			'region'                => 'Guatemala',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'503' => array(
			'region'                => 'El Salvador',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 11,
		),
		'504' => array(
			'region'                => 'Honduras',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'505' => array(
			'region'                => 'Nicaragua',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'506' => array(
			'region'                => 'Costa Rica',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'507' => array(
			'region'                => 'Panama',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 8,
		),
		'508' => array(
			'region'                => 'Saint Pierre and Miquelon',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 6,
		),
		'509' => array(
			'region'                => 'Haiti',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'51'  => array(
			'region'                => 'Peru',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 11,
		),
		'52'  => array(
			'region'                => 'Mexico',
			'min_subscriber_length' => 10,
			'max_subscriber_length' => 10,
		),
		'53'  => array(
			'region'                => 'Cuba',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 8,
		),
		'54'  => array(
			'region'                => 'Argentina',
			'min_subscriber_length' => 10,
			'max_subscriber_length' => 10,
		),
		'55'  => array(
			'region'                => 'Brazil',
			'min_subscriber_length' => 10,
			'max_subscriber_length' => 10,
		),
		'56'  => array(
			'region'                => 'Chile',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 9,
		),
		'57'  => array(
			'region'                => 'Colombia',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 10,
		),
		'58'  => array(
			'region'                => 'Venezuela',
			'min_subscriber_length' => 10,
			'max_subscriber_length' => 10,
		),
		'590' => array(
			'region'                => 'Guadeloupe',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'591' => array(
			'region'                => 'Bolivia',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'592' => array(
			'region'                => 'Guyana',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 7,
		),
		'593' => array(
			'region'                => 'Ecuador',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'594' => array(
			'region'                => 'French Guiana',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'595' => array(
			'region'                => 'Paraguay',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 9,
		),
		'596' => array(
			'region'                => 'Martinique',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'597' => array(
			'region'                => 'Suriname',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 7,
		),
		'598' => array(
			'region'                => 'Uruguay',
			'min_subscriber_length' => 4,
			'max_subscriber_length' => 11,
		),
		'599' => array(
			'region'                => 'Curacao',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 8,
		),
		'60'  => array(
			'region'                => 'Malaysia',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 9,
		),
		'61'  => array(
			'region'                => 'Australia',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 15,
		),
		'62'  => array(
			'region'                => 'Indonesia',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 10,
		),
		'63'  => array(
			'region'                => 'Philippines',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 10,
		),
		'64'  => array(
			'region'                => 'new Zealand()',
			'min_subscriber_length' => 3,
			'max_subscriber_length' => 10,
		),
		'65'  => array(
			'region'                => 'Singapore',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 12,
		),
		'66'  => array(
			'region'                => 'Thailand',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 9,
		),
		'673' => array(
			'region'                => 'Brunei',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 7,
		),
		'674' => array(
			'region'                => 'Nauru',
			'min_subscriber_length' => 4,
			'max_subscriber_length' => 7,
		),
		'675' => array(
			'region'                => 'Papua new Guinea()',
			'min_subscriber_length' => 4,
			'max_subscriber_length' => 11,
		),
		'676' => array(
			'region'                => 'Tonga',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 7,
		),
		'677' => array(
			'region'                => 'Solomon Islands',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 5,
		),
		'678' => array(
			'region'                => 'Vanuatu',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 7,
		),
		'679' => array(
			'region'                => 'Fiji',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 7,
		),
		'680' => array(
			'region'                => 'Palau',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 7,
		),
		'681' => array(
			'region'                => 'Wallis and Futuna',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 6,
		),
		'682' => array(
			'region'                => 'Cook Islands',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 5,
		),
		'683' => array(
			'region'                => 'Niue',
			'min_subscriber_length' => 4,
			'max_subscriber_length' => 4,
		),
		'685' => array(
			'region'                => 'Samoa',
			'min_subscriber_length' => 3,
			'max_subscriber_length' => 7,
		),
		'686' => array(
			'region'                => 'Kiribati',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 5,
		),
		'687' => array(
			'region'                => 'new Caledonia()',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 6,
		),
		'688' => array(
			'region'                => 'Tuvalu',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 6,
		),
		'689' => array(
			'region'                => 'French Polynesia',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 6,
		),
		'690' => array(
			'region'                => 'Tokelau',
			'min_subscriber_length' => 4,
			'max_subscriber_length' => 4,
		),
		'691' => array(
			'region'                => 'Micronesia',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 7,
		),
		'692' => array(
			'region'                => 'Marshall Islands',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 7,
		),
		'7'   => array(
			'region'                => 'Russia',
			'min_subscriber_length' => 10,
			'max_subscriber_length' => 10,
		),
		'81'  => array(
			'region'                => 'Japan',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 13,
		),
		'82'  => array(
			'region'                => 'South Korea',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 11,
		),
		'84'  => array(
			'region'                => 'Vietnam',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 10,
		),
		'850' => array(
			'region'                => 'North Korea',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 17,
		),
		'852' => array(
			'region'                => 'Hong Kong',
			'min_subscriber_length' => 4,
			'max_subscriber_length' => 9,
		),
		'853' => array(
			'region'                => 'Macau',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 8,
		),
		'855' => array(
			'region'                => 'Cambodia',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'856' => array(
			'region'                => 'Laos',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 10,
		),
		'86'  => array(
			'region'                => 'China',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 12,
		),
		'880' => array(
			'region'                => 'Bangladesh',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 10,
		),
		'886' => array(
			'region'                => 'Taiwan',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 9,
		),
		'90'  => array(
			'region'                => 'Turkey',
			'min_subscriber_length' => 10,
			'max_subscriber_length' => 10,
		),
		'91'  => array(
			'region'                => 'India',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 10,
		),
		'92'  => array(
			'region'                => 'Pakistan',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 11,
		),
		'93'  => array(
			'region'                => 'Afghanistan',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'94'  => array(
			'region'                => 'Sri Lanka',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'95'  => array(
			'region'                => 'Myanmar',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 9,
		),
		'960' => array(
			'region'                => 'Maldives',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 7,
		),
		'961' => array(
			'region'                => 'Lebanon',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 8,
		),
		'962' => array(
			'region'                => 'Jordan',
			'min_subscriber_length' => 5,
			'max_subscriber_length' => 9,
		),
		'963' => array(
			'region'                => 'Syria',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 10,
		),
		'964' => array(
			'region'                => 'Iraq',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 10,
		),
		'965' => array(
			'region'                => 'Kuwait',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 8,
		),
		'966' => array(
			'region'                => 'Saudi Arabia',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 9,
		),
		'967' => array(
			'region'                => 'Yemen',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 9,
		),
		'968' => array(
			'region'                => 'Oman',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 8,
		),
		'971' => array(
			'region'                => 'United Arab Emirates',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 9,
		),
		'972' => array(
			'region'                => 'Israel',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 9,
		),
		'973' => array(
			'region'                => 'Bahrain',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'974' => array(
			'region'                => 'Qatar',
			'min_subscriber_length' => 3,
			'max_subscriber_length' => 8,
		),
		'975' => array(
			'region'                => 'Bhutan',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 8,
		),
		'976' => array(
			'region'                => 'Mongolia',
			'min_subscriber_length' => 7,
			'max_subscriber_length' => 8,
		),
		'977' => array(
			'region'                => 'Nepal',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 9,
		),
		'98'  => array(
			'region'                => 'Iran',
			'min_subscriber_length' => 6,
			'max_subscriber_length' => 10,
		),
		'992' => array(
			'region'                => 'Tajikistan',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'993' => array(
			'region'                => 'Turkmenistan',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 8,
		),
		'994' => array(
			'region'                => 'Azerbaijan',
			'min_subscriber_length' => 8,
			'max_subscriber_length' => 9,
		),
		'995' => array(
			'region'                => 'Georgia',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'996' => array(
			'region'                => 'Kyrgyzstan',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
		'998' => array(
			'region'                => 'Uzbekistan',
			'min_subscriber_length' => 9,
			'max_subscriber_length' => 9,
		),
	);
}
