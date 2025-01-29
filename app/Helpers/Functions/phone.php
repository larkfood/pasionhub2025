<?php
/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;

/**
 * Check if a phone number is a valid mobile number for a given country
 *
 * @param string|null $phone
 * @param string|null $countryCode
 * @return bool
 */
function isValidMobileNumber(?string $phone, ?string $countryCode = null): bool
{
	if (empty($phone) || empty($countryCode)) return false;
	$phone = normalizePhoneNumber($phone, $countryCode);
	
	try {
		$phoneUtil = PhoneNumberUtil::getInstance();
		$phoneObj = $phoneUtil->parse($phone, $countryCode);
		
		$isValid = (
			$phoneUtil->isValidNumberForRegion($phoneObj, $countryCode)
			&& (
				$phoneUtil->getNumberType($phoneObj) === PhoneNumberType::MOBILE
				|| $phoneUtil->getNumberType($phoneObj) === PhoneNumberType::FIXED_LINE_OR_MOBILE
			)
		);
	} catch (\Throwable $e) {
		$isValid = false;
	}
	
	return $isValid;
}

/**
 * Check if a phone number is a possible mobile number for a given country
 *
 * @param string|null $phone
 * @param string|null $countryCode
 * @return bool
 */
function isPossibleMobileNumber(?string $phone, ?string $countryCode = null): bool
{
	if (empty($phone) || empty($countryCode)) return false;
	$phone = normalizePhoneNumber($phone, $countryCode);
	
	try {
		$phoneUtil = PhoneNumberUtil::getInstance();
		$phoneObj = $phoneUtil->parse($phone, $countryCode);
		
		$isPossibleNumber = (
			(
				$phoneUtil->isPossibleNumber($phoneObj)
				|| $phoneUtil->isValidNumberForRegion($phoneObj, $countryCode)
			)
			&& (
				$phoneUtil->getNumberType($phoneObj) === PhoneNumberType::MOBILE
				|| $phoneUtil->getNumberType($phoneObj) === PhoneNumberType::FIXED_LINE_OR_MOBILE
			)
		);
	} catch (\Throwable $e) {
		$isPossibleNumber = false;
	}
	
	return $isPossibleNumber;
}

/**
 * Check if a phone number is valid for a given country
 *
 * @param string|null $phone
 * @param string|null $countryCode
 * @return bool
 */
function isValidPhoneNumber(?string $phone, ?string $countryCode = null): bool
{
	if (empty($phone) || empty($countryCode)) return false;
	$phone = normalizePhoneNumber($phone, $countryCode);
	
	try {
		$phoneUtil = PhoneNumberUtil::getInstance();
		$phoneObj = $phoneUtil->parse($phone, $countryCode);
		
		$isValid = (
			$phoneUtil->isValidNumberForRegion($phoneObj, $countryCode)
			&& (
				$phoneUtil->getNumberType($phoneObj) === PhoneNumberType::MOBILE
				|| $phoneUtil->getNumberType($phoneObj) === PhoneNumberType::FIXED_LINE_OR_MOBILE
				|| $phoneUtil->getNumberType($phoneObj) === PhoneNumberType::FIXED_LINE
				|| $phoneUtil->getNumberType($phoneObj) === PhoneNumberType::UNKNOWN
			)
		);
	} catch (\Throwable $e) {
		$isValid = false;
	}
	
	return $isValid;
}

/**
 * Check if a phone number is a possible phone number for a given country
 *
 * @param string|null $phone
 * @param string|null $countryCode
 * @return bool
 */
function isPossiblePhoneNumber(?string $phone, ?string $countryCode = null): bool
{
	if (empty($phone) || empty($countryCode)) return false;
	$phone = normalizePhoneNumber($phone, $countryCode);
	
	try {
		$phoneUtil = PhoneNumberUtil::getInstance();
		$phoneObj = $phoneUtil->parse($phone, $countryCode);
		
		$isPossibleNumber = (
			(
				$phoneUtil->isPossibleNumber($phoneObj)
				|| $phoneUtil->isValidNumberForRegion($phoneObj, $countryCode)
			)
			&& (
				$phoneUtil->getNumberType($phoneObj) === PhoneNumberType::MOBILE
				|| $phoneUtil->getNumberType($phoneObj) === PhoneNumberType::FIXED_LINE_OR_MOBILE
				|| $phoneUtil->getNumberType($phoneObj) === PhoneNumberType::FIXED_LINE
				|| $phoneUtil->getNumberType($phoneObj) === PhoneNumberType::UNKNOWN
			)
		);
	} catch (\Throwable $e) {
		$isPossibleNumber = false;
	}
	
	return $isPossibleNumber;
}

/**
 * Get Phone's National Format
 *
 * Example: BE: 012/34.56.78 => 012 34 56 78
 *
 * @param string|null $phone
 * @param string|null $countryCode
 * @return string|null
 */
function phoneNational(?string $phone, ?string $countryCode = null): ?string
{
	$phone = normalizePhoneNumber($phone, $countryCode);
	
	try {
		$phoneUtil = PhoneNumberUtil::getInstance();
		$phoneObj = $phoneUtil->parse($phone, $countryCode);
		$phone = $phoneUtil->format($phoneObj, PhoneNumberFormat::NATIONAL);
	} catch (\Throwable $e) {
		// Keep the default value
	}
	
	return $phone;
}

/**
 * Get Phone's E164 Format
 *
 * https://en.wikipedia.org/wiki/E.164
 * https://www.twilio.com/docs/glossary/what-e164
 *
 * Example: BE: 012 34 56 78 => +3212345678
 *
 * @param string|null $phone
 * @param string|null $countryCode
 * @return string|null
 */
function phoneE164(?string $phone, ?string $countryCode = null): ?string
{
	$phone = normalizePhoneNumber($phone, $countryCode);
	
	try {
		$phoneUtil = PhoneNumberUtil::getInstance();
		$phoneObj = $phoneUtil->parse($phone, $countryCode);
		$phone = $phoneUtil->format($phoneObj, PhoneNumberFormat::E164);
	} catch (\Throwable $e) {
		// Keep the default value
	}
	
	return $phone;
}

/**
 * Get Phone's International Format
 * Don't need to be saved in database
 *
 * Example: BE: 012 34 56 78 => +32 12 34 56 78
 *
 * @param string|null $phone
 * @param string|null $countryCode
 * @return string|null
 */
function phoneIntl(?string $phone, ?string $countryCode = null): ?string
{
	$phone = normalizePhoneNumber($phone, $countryCode);
	
	try {
		$phoneUtil = PhoneNumberUtil::getInstance();
		$phoneObj = $phoneUtil->parse($phone, $countryCode);
		$phone = $phoneUtil->format($phoneObj, PhoneNumberFormat::INTERNATIONAL);
	} catch (\Throwable $e) {
		// Keep the default value
	}
	
	return $phone;
}

/**
 * Get a country dial number (phone prefix) by its ISO code
 *
 * @param $countryCode
 * @return int|null
 */
function getCountryDialCode($countryCode): ?int
{
	try {
		$phoneUtil = PhoneNumberUtil::getInstance();
		
		return $phoneUtil->getCountryCodeForRegion($countryCode);
	} catch (\Throwable $e) {
	}
	
	return null;
}

/**
 * Get an example phone number related to a country
 *
 * @param string|null $countryCode
 * @param string|int|null $type
 * @return string|null
 */
function getExamplePhoneNumber(?string $countryCode = null, string|int|null $type = 'MOBILE'): ?string
{
	$phone = null;
	
	try {
		$phoneUtil = PhoneNumberUtil::getInstance();
		
		$phoneObj = null;
		if (!empty($type)) {
			if (is_string($type)) {
				$constantName = '\libphonenumber\PhoneNumberType::' . $type;
				if (defined($constantName)) {
					$phoneNumberType = constant($constantName);
					$phoneObj = $phoneUtil->getExampleNumberForType($countryCode, $phoneNumberType);
				}
			} else {
				$phoneObj = $phoneUtil->getExampleNumberForType($countryCode, $type);
			}
		} else {
			$phoneObj = $phoneUtil->getExampleNumber($countryCode);
		}
		
		if (!is_null($phoneObj)) {
			$phone = $phoneUtil->format($phoneObj, PhoneNumberFormat::NATIONAL);
		}
	} catch (\Throwable $e) {
	}
	
	return $phone;
}

/**
 * Get phone's normal format
 *
 * i.e. Get a value close to the one entered by the user or one that the user
 * should have entered. This is equivalent to getting a value similar to the
 * national format without spaces, allowing to format recursively the phone numbers
 *
 * Example:
 * - BE: 012/34.56.78 => 012345678
 * - DE: 0049 15510 686794 => 15510686794
 * - DE: +49 15510 686794 => 15510686794
 * - DE: 49 15510 686794 => 4915510686794 (failed normalization)
 *
 * @param string|null $phone
 * @param string|null $countryCode
 * @return string
 */
function normalizePhoneNumber(?string $phone, ?string $countryCode = null): string
{
	// Remove all non-digit characters except +
	$phone = sanitizePhoneNumberForE164($phone);
	
	// Replace 00 by + at the phone beginning
	if (str_starts_with($phone, '00')) {
		$phone = str($phone)->replaceStart('00', '+')->toString();
	}
	
	/*
	 * Remove the + character and the country dial number from the phone beginning
	 * e.g. +41 will be removed from +419876543210
	 *      41 will not be removed from 419876543210
	 */
	if (!empty($countryCode)) {
		$countryDialCode = getCountryDialCode($countryCode);
		if (!empty($countryDialCode)) {
			$countryDialCode = str($countryDialCode)->start('+')->toString();
			
			if (str_starts_with($phone, $countryDialCode)) {
				$phone = str($phone)->replaceStart($countryDialCode, '')->toString();
			}
		}
	}
	
	return getAsString($phone);
}

/**
 * Remove all non-digit characters except +
 *
 * @param string|null $phone
 * @return string
 */
function sanitizePhoneNumberForE164(?string $phone): string
{
	$phone = preg_replace('/[^\d+]/', '', strval($phone));
	
	return getAsString($phone);
}

/**
 * Remove all non-digit characters from the phone number
 *
 * @param string|null $phone
 * @return string
 */
function sanitizePhoneNumberForNational(?string $phone): string
{
	$phone = preg_replace('/\D+/', '', strval($phone));
	
	return getAsString($phone);
}

/**
 * @param string|null $phone
 * @param string|null $provider
 * @return string|null
 */
function setPhoneSign(?string $phone, ?string $provider = null): ?string
{
	$phone = strval($phone);
	
	if ($provider == 'vonage') {
		// Vonage doesn't support the sign '+'
		if (str_starts_with($phone, '+')) {
			$phone = str($phone)->replaceStart('+', '')->toString();
		}
	}
	
	if ($provider == 'twilio') {
		// Twilio requires the sign '+'
		if (!str_starts_with($phone, '+')) {
			$phone = '+' . $phone;
		}
	}
	
	if (!in_array($provider, ['vonage', 'twilio'])) {
		if (!str_starts_with($phone, '+')) {
			$phone = '+' . $phone;
		}
	}
	
	return ($phone == '+') ? '' : $phone;
}
