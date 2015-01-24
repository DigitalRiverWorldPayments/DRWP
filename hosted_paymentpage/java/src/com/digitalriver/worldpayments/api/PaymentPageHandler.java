package com.digitalriver.worldpayments.api;

import java.util.Map;

import com.digitalriver.worldpayments.api.security.SecurityHandler;
import com.digitalriver.worldpayments.api.security4.JKSKeyHandler;
import com.digitalriver.worldpayments.api.security4.SecurityHandlerImpl;
import com.digitalriver.worldpayments.api.utils.ParseUtil;

/**
 * The responsibility of this class is to create a redirect URL (when
 * redirecting the consumer to PaymentPage), and unpack the response URL (that
 * comes back when consumer has been redirected back to Merchant)
 * @see PaymentPageRequest
 * @see PaymentPageResponse
 */
public class PaymentPageHandler {

    public static final String DEFAULT_PRODUCTION_BASE_URL = "https://secure.payments.digitalriver.com/pay/?creq=";

    public static final String DEFAULT_TEST_BASE_URL = "https://testpage.payments.digitalriver.com/pay/?creq=";

    static final PaymentPageResponse createPaymentPageResponse(
            final Map<String, String> nvp) {

        PaymentPageResponse ppResponse = new PaymentPageResponse();
        try {
            ParameterAnnotationHelper.mapNvpToObject(ppResponse, nvp);
        } catch (Exception e) {
            throw new IllegalArgumentException("Contact DRWP support",
                    e);
        }

        return ppResponse;
    }

    private final String iBaseUrl;

    private final SecurityHandler iSecurityHandler;

    /**
     * Create a PaymentPageHandler with a specified key handler The
     * PaymentPageHandler is then used to create a redirect URL used when
     * redirecting consumer to PaymentPage.
     * 
     * @param aBaseUrl
     *            @see #DEFAULT_PRODUCTION_BASE_URL
     *            @see #DEFAULT_TEST_BASE_URL
     * @param aKeyHandler
     *            a KeyHandler containing payment page keys
     */
    public PaymentPageHandler(String aBaseUrl, JKSKeyHandler aKeyHandler) {
        this(aBaseUrl, new SecurityHandlerImpl(aKeyHandler));
    }

    PaymentPageHandler(String aBaseUrl, SecurityHandler aSecurityHandler) {
        iBaseUrl = aBaseUrl;
        iSecurityHandler = aSecurityHandler;
    }

    /**
     * Method used for creating the the redirect URL used for redirecting
     * consumers to PaymentPage.
     * 
     * @param request
     *            The request containing required data for initiate a payment in
     *            DigitalRiver WorldPayments system
     * @return The encrypted URL string that should be used when redirecting
     *         consumer to PaymentPage
     */
    public String createRedirectUrl(PaymentPageRequest request)
            throws IllegalArgumentException {
        StringBuilder url = new StringBuilder();
        url.append(iBaseUrl);
        Map<String, String> nvp = ParameterAnnotationHelper.mapObjectToNvp(request);
        addVersionField(nvp);
        url.append(iSecurityHandler.encrypt(ParameterAnnotationHelper.createNvpString(nvp)));
        return url.toString();
    }

    private static void addVersionField(Map<String, String> map) {
        map.put("VER", "2.5.0");
    }

    /**
     * When PaymentPage is done, it redirects the consumer back to the
     * returnUrl with a response string. The returnUrl can be
     * set using {@link PaymentPageRequest#setReturnUrl}. The response
     * string is encrypted and can be decrypted with the following method.
     * 
     * Be sure to have the response URLDecoded before calling this method
     * 
     * @param encodedResponseString
     *            the encrypted response string received from PaymentPageServer
     * @return the decrypted PaymentResponse object
     */
    public PaymentPageResponse unpackResponse(String encodedResponseString) {

        String decodedResponse = iSecurityHandler
                .decrypt(encodedResponseString);

        Map<String, String> nvpMap = ParseUtil.parseWithEscape(decodedResponse,
                '=', ';');

        return createPaymentPageResponse(nvpMap);
    }
}
