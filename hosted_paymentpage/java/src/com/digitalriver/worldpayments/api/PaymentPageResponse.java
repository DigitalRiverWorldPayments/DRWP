package com.digitalriver.worldpayments.api;

import java.util.Date;

import com.digitalriver.worldpayments.api.utils.Parameter;

/**
 * Class that contains the response that comes back from PaymentPage when
 * consumer has been redirected back to Merchant
 * @see PaymentPageHandler
 */
public class PaymentPageResponse {

    @Parameter(shortName = "A")
    Long mid;

    @Parameter(shortName = "E")
    String orderId;

    @Parameter(shortName = "T")
    Boolean redirected;

    @Parameter(shortName = "B")
    String status;

    @Parameter(shortName = "F")
    Date timestamp;

    @Parameter(shortName= "AC")
    Long avsAnswerCode;

    @Parameter(shortName= "AD")
    String avsResponse;

    @Parameter(shortName= "AE")
    String acquirerAnswerCode;

    @Parameter(shortName= "AF")
    Long clientAnswerCode;

    @Parameter(shortName= "AG")
    Long cvAnswerCode;

    @Parameter(shortName= "AH")
    String cvResponse;

    @Parameter(shortName= "U")
    String maskedAccountNumber;

    @Parameter(shortName= "P")
    String expirationDate;

    @Parameter(shortName= "M")
    String cardType;

    @Parameter(shortName= "AI")
    String paymentMethodName;

    @Parameter(shortName= "AJ")
    String acquirerAuthCode;

    TokenizationResult tokenizationResult;

    Transaction transaction;

    PaymentPageResponse() {
    }

    public Long getMid() {
        return mid;
    }

    public String getOrderId() {
        return orderId;
    }

    /**
     * The status of this PaymentPage session
     * OK/NOK/ERROR/USERCANCEL/PENDING
     */
    public String getStatus() {
        return status;
    }

    /**
     * The time stamp of when the PaymentPageResponse was created It's created
     * just before the consumer is redirected back to merchant.
     */
    public Date getTimestamp() {
        return timestamp;
    }

    public TokenizationResult getTokenizationResult() {
        return tokenizationResult;
    }

    /**
     * Transaction created in this PaymentPage session
     */
    public Transaction getTransaction() {
        return transaction;
    }

    /**
     * Redirected status, true if the consumer was redirected to a third party
     * site during the session at Payment Page
     */
    public boolean wasRedirected() {
        return redirected.booleanValue();
    }

    /**
     * AVS Answer Code. The response value will only be present if AVS is enabled for the payment type.
     */
    public Long getAvsAnswerCode() {
        return avsAnswerCode;
    }

    /**
     * AVS Response. The response value will only be present if AVS is enabled for the payment type.
     */
    public String getAvsResponse() {
        return avsResponse;
    }

    /**
     * Acquirer Answer Code. This is the bank or payment providers answer code.
     */
    public String getAcquirerAnswerCode() {
        return acquirerAnswerCode;
    }

    /**
     * Client Answer Code. This is the Digital River World Payments internal answer code
     */
    public Long getClientAnswerCode() {
        return clientAnswerCode;
    }

    /**
     * CVV Answer Code.This answer code will only be present for card payments.
     */
    public Long getCvAnswerCode() {
        return cvAnswerCode;
    }

    /**
     * CVV Response. The response value will only be present for card payments.
     */
    public String getCvResponse() {
        return cvResponse;
    }

    /**
     * Masked account number (typlically masked card number). The response value will only be present for card payments.
     */
    public String getMaskedAccountNumber() {
        return maskedAccountNumber;
    }

    /**
     * Expiration date for account (card). The response value will only be present for card payments.
     */
    public String getExpirationDate() {
        return expirationDate;
    }

    /**
     * Get card type (legacy from ws2006)
     * @return visa or mastercard for example (unknown case)
     * @deprecated used getPaymentMethodName instead
     */
    @Deprecated
    public String getCardType() {
        return cardType;
    }

    /**
     * Auth code from acquirer (typically card payments)
     * @return authCode
     */
    public String getAcquirerAuthCode() {
        return acquirerAuthCode;
    }

    /**
     * Name of payment method as returned from payment method config service
     * Visa/Mastercard/Nordea...
     * @return name
     */
    public String getPaymentMethodName() {
        return paymentMethodName;
    }

}