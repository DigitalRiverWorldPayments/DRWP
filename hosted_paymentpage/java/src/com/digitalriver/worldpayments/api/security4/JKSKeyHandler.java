package com.digitalriver.worldpayments.api.security4;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.InputStream;
import java.security.KeyPair;
import java.security.KeyStore;
import java.security.PrivateKey;
import java.security.PublicKey;
import java.security.cert.X509Certificate;
import java.util.Date;
import java.util.Enumeration;
import java.util.Hashtable;
import java.util.Map;

public class JKSKeyHandler {
	private static byte[] longTo4Bytes(Long aLong) {
		long val = aLong.longValue();

		if (val < 0) {
			throw new IllegalArgumentException("Negative value");
		}
		if (val > Integer.MAX_VALUE * 2L) {
			throw new IllegalArgumentException("Value to large!");
		}

		return new byte[] { (byte) (val >> 24 & 0xFF),
				(byte) ((val >> 16) & 0xFF), (byte) (val >> 8 & 0xFF),
				(byte) (val & 0xFF) };
	}

	private KeyPair iMerchantKeyPair;

	private Map<Long, KeyPair> iMerchantKeyPairMap;

	private byte[] iMerchantKeyPairSerialNo;

	private PublicKey iPublicKey;

	private Map<Long, PublicKey> iPublicKeyMap;

	private byte[] iPublicKeySerialNo;

	/**
	 * A KeyHandler that reads private keys and public certificates from a Java
	 * KeyStore
	 * 
	 * @param aKeyStore
	 *            path to the Java KeyStore to use
	 * @param aKeyStorePwd
	 *            password of the KeyStore
	 * @param aKeyName
	 *            name of the private key
	 * @param aCertificateName
	 *            name of the certificate
	 */
	public JKSKeyHandler(String aKeyStore, String aKeyStorePwd,
			String aKeyName, String aCertificateName) {
		iMerchantKeyPairMap = new Hashtable<Long, KeyPair>();
		iPublicKeyMap = new Hashtable<Long, PublicKey>();
		KeyStore ks = loadKeyStore(aKeyStore, aKeyStorePwd);
		init(ks, aKeyName, aKeyStorePwd, aCertificateName);
	}

	KeyPair getKeyPair() {
		return iMerchantKeyPair;
	}

	KeyPair getKeyPair(long aSerialNo) {
		return iMerchantKeyPairMap.get(new Long(aSerialNo));
	}

	byte[] getKeyPairSerialNo() {
		return iMerchantKeyPairSerialNo;
	}

	PublicKey getNetgiroKey() {
		return iPublicKey;
	}

	PublicKey getNetgiroKey(long aSerialNo) {
		return iPublicKeyMap.get(new Long(aSerialNo));
	}

	byte[] getNetgiroKeySerialNo() {
		return iPublicKeySerialNo;
	}

	private void init(KeyStore aKeyStore, String aKeyName, String aKeyPwd,
			String aCertificateName) {
		try {
			Enumeration<String> aliases = aKeyStore.aliases();
			Long minId = new Long(Long.MAX_VALUE);
			Date newest = new Date(0);

			while (aliases.hasMoreElements()) {
				String alias = aliases.nextElement();

				if (alias.startsWith(aKeyName)) {
					PrivateKey pKey = (PrivateKey) aKeyStore.getKey(alias,
							aKeyPwd.toCharArray());
					X509Certificate cert = (X509Certificate) aKeyStore
							.getCertificate(alias);

					if (pKey == null || cert == null) {
						continue;
					}
					Long serialNo = new Long(cert.getSerialNumber().longValue());
					iMerchantKeyPairMap.put(serialNo, new KeyPair(cert.getPublicKey(),
							pKey));

					// Check if the certificate is most recent
					if (cert.getNotBefore().after(newest)) {
						newest = cert.getNotBefore();
						minId = serialNo;
					}
				}
			}

			if (minId.longValue() == Long.MAX_VALUE) {
				throw new NullPointerException(
						"No matching keypair found in keystore!");
			}

			iMerchantKeyPair = iMerchantKeyPairMap.get(minId);
			iMerchantKeyPairSerialNo = longTo4Bytes(minId);
		} catch (Exception e) {
			throw new IllegalArgumentException("Could not access key "
					+ aKeyName + " from keystore. Cause: " + e.getMessage());
		}

		try {
			Enumeration<String> aliases = aKeyStore.aliases();
			Long minId = new Long(Long.MAX_VALUE);
			Date newest = new Date(0);

			while (aliases.hasMoreElements()) {
				String alias = aliases.nextElement();

				if (alias.startsWith(aCertificateName)) {
					X509Certificate cert = (X509Certificate) aKeyStore
							.getCertificate(alias);

					if (cert == null) {
						continue;
					}
					Long serialNo = new Long(cert.getSerialNumber().longValue());
					iPublicKeyMap.put(serialNo, cert.getPublicKey());

					// Check if the certificate is most recent
					if (cert.getNotBefore().after(newest)) {
						newest = cert.getNotBefore();
						minId = serialNo;
					}
				}
			}
			if (minId.longValue() == Long.MAX_VALUE) {
				throw new NullPointerException(
						"No matching trusted certificate found in keystore!");
			}
			iPublicKey = iPublicKeyMap.get(minId);
			iPublicKeySerialNo = longTo4Bytes(minId);
		} catch (Exception e) {
			throw new IllegalArgumentException(
					"Could not access netgiro certificate: "
							+ aCertificateName
							+ " from keystore. Cause: " + e.getMessage());
		}
	}

	private KeyStore loadKeyStore(String aKeyStore, String aKeyStorePwd) {
		KeyStore keyStore;
		InputStream keyStoreStream;

		try {
			keyStoreStream = new FileInputStream(new File(aKeyStore));
		} catch (FileNotFoundException e1) {
			keyStoreStream = ClassLoader.getSystemResourceAsStream(aKeyStore);

			if (keyStoreStream == null) {
				throw new IllegalArgumentException(
						"Could not locate KeyStore: " + aKeyStore);
			}
		}

		try {
			keyStore = KeyStore.getInstance("JKS");
		} catch (Exception e) {
			throw new IllegalStateException(
					"Could not instantiate keystore! Cause: " + e.getMessage());
		}

		try {
			keyStore.load(keyStoreStream, aKeyStorePwd.toCharArray());
		} catch (Exception e) {
			throw new IllegalArgumentException("Could not access keystore: "
					+ aKeyStore + ". Cause: " + e.getMessage());
		}
		return keyStore;
	}
}
