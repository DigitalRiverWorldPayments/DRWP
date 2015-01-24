package com.digitalriver.worldpayments.api.security4;

import java.io.UnsupportedEncodingException;
import java.math.BigInteger;
import java.security.KeyPair;
import java.security.PublicKey;

import javax.crypto.SecretKey;

import com.digitalriver.worldpayments.api.security.SecurityHandler;
import com.digitalriver.worldpayments.api.security.SecurityHandlerException;
import com.digitalriver.worldpayments.api.utils.Base64Utils;
import com.digitalriver.worldpayments.api.utils.CryptoUtils;
import com.digitalriver.worldpayments.api.utils.CryptoUtils.CryptoException;

public class SecurityHandlerImpl implements SecurityHandler {

	private static final String ENCODING_UTF_8 = "UTF-8";

	public static final byte RSA_1024_AES_128_ENC_MODE_V4 = 4;

	private Base64Utils iEncoder;

	private JKSKeyHandler iKeyHandler;

	public SecurityHandlerImpl(JKSKeyHandler aKeyHandler) {
		iKeyHandler = aKeyHandler;
		iEncoder = new Base64Utils();
	}

	/* (non-Javadoc)
	 * @see com.digitalriver.worldpayments.api.securityv4.SecurityHandlerI#decrypt(java.lang.String)
	 */
	public String decrypt(String aRedirect) {
		byte[] encKey = new byte[128];
		byte[] signature = new byte[128];
		byte[] cipherText;
		byte[] envelope;
		KeyPair privKey;
		PublicKey ngKey;

		envelope = iEncoder.decode(aRedirect);

		if (envelope[0] != RSA_1024_AES_128_ENC_MODE_V4) {
			try {
				envelope = CryptoUtils.unzip(envelope);
			} catch (CryptoException e) {
				// Probably corrupt indata
				throw new SecurityHandlerException("Failed to unzip envelope!",
						e);
			}
		}

		switch (envelope[0]) {
		case RSA_1024_AES_128_ENC_MODE_V4:
			if (envelope.length < 266) {
				throw new SecurityHandlerException(
						"Invalid envelope: Too short! Len=" + envelope.length);
			}

			/*
			 * Chunks: [1byte]+[8bytes]+[8bytes]+[128bytes]+[128bytes]+[1..n
			 * bytes]
			 */
			/*
			 * Content:
			 * <type>+<serial#>+<serial#>+<key>+<signature>+<encrypted_data>
			 */
			cipherText = new byte[envelope.length - 265];

			byte[] receiverSN = new byte[4];
			byte[] senderSN = new byte[4];

			System.arraycopy(envelope, 1, receiverSN, 0, 4);
			System.arraycopy(envelope, 5, senderSN, 0, 4);
			System.arraycopy(envelope, 9, encKey, 0, 128);
			System.arraycopy(envelope, 137, signature, 0, 128);
			System.arraycopy(envelope, 265, cipherText, 0, cipherText.length);

			privKey = iKeyHandler.getKeyPair(new BigInteger(receiverSN)
					.longValue());
			ngKey = iKeyHandler.getNetgiroKey(new BigInteger(senderSN)
					.longValue());
			break;
		default:
			throw new SecurityHandlerException(
					"Invalid envelope! Unknown mode: " + envelope[0]);
		}

		if (privKey == null) {
			throw new SecurityHandlerException(
					"Could not retrieve merchant key!");
		}
		if (ngKey == null) {
			throw new SecurityHandlerException(
					"Could not retrieve netgiro key!");
		}

		byte[] key;
		byte[] plainText;
		SecretKey secretKey;

		try {
			key = CryptoUtils.decryptAsymmetric(privKey.getPrivate(), encKey);
		} catch (CryptoException e) {
			throw new SecurityHandlerException(
					"Failed to retrieve encrypted key!", e);
		}

		secretKey = CryptoUtils.restoreKey(key);

		try {
			plainText = CryptoUtils.decryptSymmetric(secretKey, cipherText);
		} catch (CryptoException e) {
			throw new SecurityHandlerException(
					"Failed to decrypt envelope content!", e);
		}

		if (envelope[0] == RSA_1024_AES_128_ENC_MODE_V4) {
			try {
				plainText = CryptoUtils.unzip(plainText);
			} catch (CryptoException e) {
				throw new SecurityHandlerException(
						"Failed to unzip plaintext!", e);
			}
		}

		try {
			if (!CryptoUtils.verifySignature(ngKey, cipherText, signature)) {
				throw new SecurityHandlerException("Signature does not match!");
			}
		} catch (CryptoException e) {
			throw new SecurityHandlerException("Failed to verify signature!", e);
		}

		try {
			return new String(plainText, ENCODING_UTF_8);
		} catch (UnsupportedEncodingException e) {
			// Should never happen
			throw new SecurityHandlerException(
					"Failed to convert result to UTF-8", e);
		}
	}

	/* (non-Javadoc)
	 * @see com.digitalriver.worldpayments.api.securityv4.SecurityHandlerI#encrypt(java.lang.String)
	 */
	public String encrypt(String aRedirect) throws SecurityHandlerException {
		byte[] plainText;
		byte[] cipherText;
		byte[] signature;
		byte[] encryptedKey;
		byte[] merchantSerialNo;
		byte[] ngSerialNo;
		SecretKey key;
		PublicKey ngKey;

		// Fetch netgiro key and serial numbers
		ngKey = iKeyHandler.getNetgiroKey();
		ngSerialNo = iKeyHandler.getNetgiroKeySerialNo();
		merchantSerialNo = iKeyHandler.getKeyPairSerialNo();

		try {
			plainText = aRedirect.getBytes(ENCODING_UTF_8);
		} catch (UnsupportedEncodingException e) {
			// Should never happen
			throw new SecurityHandlerException(
					"Could not extract redirect as UTF-8", e);
		}

		try {
			// Create message key
			key = CryptoUtils.createKey(128);
		} catch (CryptoException e) {
			throw new SecurityHandlerException("Failed to create secret key", e);
		}

		// Zip content
		try {
			plainText = CryptoUtils.zip(plainText);
		} catch (CryptoException e) {
			throw new SecurityHandlerException("Failed to zip content data!", e);
		}

		try {
			// Encrypt message, i.e. redirect content
			cipherText = CryptoUtils.encryptSymmetric(key, plainText);
		} catch (CryptoException e) {
			throw new SecurityHandlerException(
					"Failed to encrypt redirect data!", e);
		}

		try {
			// Create a signature of the encrypted data
			signature = CryptoUtils.createSignature(iKeyHandler.getKeyPair()
					.getPrivate(), cipherText);

			// verify length
			if (signature.length != 128) {
				throw new SecurityHandlerException(
						"Invalid signature length! Len=" + signature.length);
			}
		} catch (CryptoException e) {
			throw new SecurityHandlerException("Failed to create signature!", e);
		}

		try {
			// Encrypt message key with merchant public key
			encryptedKey = CryptoUtils.encryptAsymmetric(ngKey,
					key.getEncoded());

			// verify length
			if (encryptedKey.length != 128) {
				throw new SecurityHandlerException(
						"Invalid encrypted key length! Len="
								+ encryptedKey.length);
			}
		} catch (CryptoException e) {
			throw new SecurityHandlerException(
					"Failed to encrypt message key!", e);
		}

		int startpos = 1;

		byte[] result = new byte[1 + 8 + encryptedKey.length + signature.length
				+ cipherText.length];

		// Assign mode
		result[0] = RSA_1024_AES_128_ENC_MODE_V4;

		// Pack data
		System.arraycopy(ngSerialNo, 0, result, startpos, 4);
		startpos += 4;

		System.arraycopy(merchantSerialNo, 0, result, startpos, 4);
		startpos += 4;

		System.arraycopy(encryptedKey, 0, result, startpos, encryptedKey.length);
		startpos += encryptedKey.length;

		System.arraycopy(signature, 0, result, startpos, signature.length);
		startpos += signature.length;

		System.arraycopy(cipherText, 0, result, startpos, cipherText.length);

		return iEncoder.encode(result);
	}

}
