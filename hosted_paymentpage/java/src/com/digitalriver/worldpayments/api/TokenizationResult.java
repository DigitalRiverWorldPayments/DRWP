package com.digitalriver.worldpayments.api;

import com.digitalriver.worldpayments.api.utils.Parameter;

public class TokenizationResult {

	@Parameter(shortName = "W")
	String expirationDate;

	@Parameter(shortName = "V")
	String maskedAccountNumber;

	@Parameter(shortName = "N")
	String token;

	public String getExpirationDate() {
		return expirationDate;
	}

	public String getMaskedAccountNumber() {
		return maskedAccountNumber;
	}

	public String getToken() {
		return token;
	}

}
