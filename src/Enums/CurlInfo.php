<?php declare(strict_types=1);

namespace EasyHttp\Enums;

use Utilities\Common\Traits\hasAssocStorage;

/**
 * CurlInfo class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 *
 * @method string   getURL()                        This method returns the last effective URL.
 * @method string   getContentType()                This method returns the content-type of the requested document.
 * @method int      getHttpCode()                   This method returns the last received HTTP code.
 * @method int      getHeaderSize()                 This method returns the total size of all headers received.
 * @method int      getRequestSize()                This method returns the total size of all issued requests.
 * @method int      getFileTime()                   This method returns the remote time of the retrieved document.
 * @method int      getSSLVerifyResult()            This method returns the result of the SSL certification verification that was requested.
 * @method int      getRedirectCount()              This method returns the total number of redirects that were followed.
 * @method float    getTotalTime()                  This method returns the total transaction time in seconds for last transfer.
 * @method float    getNamelookupTime()             This method returns the time, in seconds, it took from the start until the name resolving was completed.
 * @method float    getConnectTime()                This method returns the time, in seconds, it took from the start until the connect to the remote host ( or proxy) was completed.
 * @method float    getPreTransferTime()            This method returns the time, in seconds, it took from the start until the file transfer is just about to begin.
 * @method int      getSizeUpload()                 This method returns the total number of bytes that were uploaded.
 * @method int      getSizeDownload()               This method returns the total number of bytes that were downloaded.
 * @method int      getSpeedDownload()              This method returns the average download speed that curl measured for the complete download.
 * @method int      getSpeedUpload()                This method returns the average upload speed that curl measured for the complete upload.
 * @method int      getDownloadContentLength()      This method returns the content-length of the download.
 * @method int      getUploadContentLength()        This method returns the content-length of the upload.
 * @method float    getStartTransferTime()          This method returns the time, in seconds, it took from the start until the first byte is just about to be transferred.
 * @method int      getRedirectTime()               This method returns the time, in seconds, it took for all redirection steps include name lookup, connect, pretransfer and transfer before final transaction was started.
 * @method string   getRedirectURL()                This method returns the complete effective URL that was last used.
 * @method int      getPrimaryIP()                  This method returns the IP address of the most recent connection.
 * @method array    getCertInfo()                   This method returns an array with the certificate information.
 * @method int      getPrimaryPort()                This method returns the remote port of the most recent connection.
 * @method string   getLocalIP()                    This method returns the local IP address of the most recent connection.
 * @method int      getLocalPort()                  This method returns the local port of the most recent connection.
 * @method int      getHttpVersion()                This method returns the version of the last used request.
 * @method int      getProtocol()                   This method returns the protocol used for the last transfer.
 * @method string   getScheme()                     This method returns the scheme used for the last transfer.
 * @method string   getAppconnectTimeUs()           This method returns the time, in seconds, it took from the start until the SSL/SSH/etc connect/handshake to the remote host was completed.
 * @method string   getConnectTimeUs()              This method returns the time, in seconds, it took from the start until the connect to the remote host ( or proxy) was completed.
 * @method string   getNamelookupTimeUs()           This method returns the time, in seconds, it took from the start until the name resolving was completed.
 * @method string   getPretransferTimeUs()          This method returns the time, in seconds, it took from the start until the file transfer is just about to begin.
 * @method string   getRedirectTimeUs()             This method returns the time, in seconds, it took for all redirection steps include name lookup, connect, pretransfer and transfer before final transaction was started.
 * @method string   getStarttransferTimeUs()        This method returns the time, in seconds, it took from the start until the first byte is just about to be transferred.
 * @method string   getTotalTimeUs()                This method returns the total transaction time in seconds for last transfer.
 */
class CurlInfo extends \Utilities\Common\Entity
{

    use hasAssocStorage;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->formatKey($key);
        }
        parent::__construct($data);
    }

    /**
     * Key formatter
     *
     * @param string $key
     * @return string
     */
    protected function formatKey(string $key): string
    {
        // Converts from UPPER_CASE to snake_case
        return mb_strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', mb_strtoupper($key)));
    }

}