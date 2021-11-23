<?php

namespace RectorPrefix20211123\React\Dns\Protocol;

use RectorPrefix20211123\React\Dns\Model\Message;
use RectorPrefix20211123\React\Dns\Model\Record;
use RectorPrefix20211123\React\Dns\Query\Query;
use InvalidArgumentException;
/**
 * DNS protocol parser
 *
 * Obsolete and uncommon types and classes are not implemented.
 */
final class Parser
{
    /**
     * Parses the given raw binary message into a Message object
     *
     * @param string $data
     * @throws InvalidArgumentException
     * @return Message
     */
    public function parseMessage($data)
    {
        // create empty message with two additional, temporary properties for parser
        $message = new \RectorPrefix20211123\React\Dns\Model\Message();
        $message->data = $data;
        $message->consumed = null;
        if ($this->parse($data, $message) !== $message) {
            throw new \InvalidArgumentException('Unable to parse binary message');
        }
        unset($message->data, $message->consumed);
        return $message;
    }
    private function parse($data, \RectorPrefix20211123\React\Dns\Model\Message $message)
    {
        if (!isset($message->data[12 - 1])) {
            return;
        }
        list($id, $fields, $qdCount, $anCount, $nsCount, $arCount) = \array_values(\unpack('n*', \substr($message->data, 0, 12)));
        $message->consumed += 12;
        $message->id = $id;
        $message->rcode = $fields & 0xf;
        $message->ra = ($fields >> 7 & 1) === 1;
        $message->rd = ($fields >> 8 & 1) === 1;
        $message->tc = ($fields >> 9 & 1) === 1;
        $message->aa = ($fields >> 10 & 1) === 1;
        $message->opcode = $fields >> 11 & 0xf;
        $message->qr = ($fields >> 15 & 1) === 1;
        // parse all questions
        for ($i = $qdCount; $i > 0; --$i) {
            $question = $this->parseQuestion($message);
            if ($question === null) {
                return;
            } else {
                $message->questions[] = $question;
            }
        }
        // parse all answer records
        for ($i = $anCount; $i > 0; --$i) {
            $record = $this->parseRecord($message);
            if ($record === null) {
                return;
            } else {
                $message->answers[] = $record;
            }
        }
        // parse all authority records
        for ($i = $nsCount; $i > 0; --$i) {
            $record = $this->parseRecord($message);
            if ($record === null) {
                return;
            } else {
                $message->authority[] = $record;
            }
        }
        // parse all additional records
        for ($i = $arCount; $i > 0; --$i) {
            $record = $this->parseRecord($message);
            if ($record === null) {
                return;
            } else {
                $message->additional[] = $record;
            }
        }
        return $message;
    }
    /**
     * @param Message $message
     * @return ?Query
     */
    private function parseQuestion(\RectorPrefix20211123\React\Dns\Model\Message $message)
    {
        $consumed = $message->consumed;
        list($labels, $consumed) = $this->readLabels($message->data, $consumed);
        if ($labels === null || !isset($message->data[$consumed + 4 - 1])) {
            return;
        }
        list($type, $class) = \array_values(\unpack('n*', \substr($message->data, $consumed, 4)));
        $consumed += 4;
        $message->consumed = $consumed;
        return new \RectorPrefix20211123\React\Dns\Query\Query(\implode('.', $labels), $type, $class);
    }
    /**
     * @param Message $message
     * @return ?Record returns parsed Record on success or null if data is invalid/incomplete
     */
    private function parseRecord(\RectorPrefix20211123\React\Dns\Model\Message $message)
    {
        $consumed = $message->consumed;
        list($name, $consumed) = $this->readDomain($message->data, $consumed);
        if ($name === null || !isset($message->data[$consumed + 10 - 1])) {
            return null;
        }
        list($type, $class) = \array_values(\unpack('n*', \substr($message->data, $consumed, 4)));
        $consumed += 4;
        list($ttl) = \array_values(\unpack('N', \substr($message->data, $consumed, 4)));
        $consumed += 4;
        // TTL is a UINT32 that must not have most significant bit set for BC reasons
        if ($ttl < 0 || $ttl >= 1 << 31) {
            $ttl = 0;
        }
        list($rdLength) = \array_values(\unpack('n', \substr($message->data, $consumed, 2)));
        $consumed += 2;
        if (!isset($message->data[$consumed + $rdLength - 1])) {
            return null;
        }
        $rdata = null;
        $expected = $consumed + $rdLength;
        if (\RectorPrefix20211123\React\Dns\Model\Message::TYPE_A === $type) {
            if ($rdLength === 4) {
                $rdata = \inet_ntop(\substr($message->data, $consumed, $rdLength));
                $consumed += $rdLength;
            }
        } elseif (\RectorPrefix20211123\React\Dns\Model\Message::TYPE_AAAA === $type) {
            if ($rdLength === 16) {
                $rdata = \inet_ntop(\substr($message->data, $consumed, $rdLength));
                $consumed += $rdLength;
            }
        } elseif (\RectorPrefix20211123\React\Dns\Model\Message::TYPE_CNAME === $type || \RectorPrefix20211123\React\Dns\Model\Message::TYPE_PTR === $type || \RectorPrefix20211123\React\Dns\Model\Message::TYPE_NS === $type) {
            list($rdata, $consumed) = $this->readDomain($message->data, $consumed);
        } elseif (\RectorPrefix20211123\React\Dns\Model\Message::TYPE_TXT === $type || \RectorPrefix20211123\React\Dns\Model\Message::TYPE_SPF === $type) {
            $rdata = array();
            while ($consumed < $expected) {
                $len = \ord($message->data[$consumed]);
                $rdata[] = (string) \substr($message->data, $consumed + 1, $len);
                $consumed += $len + 1;
            }
        } elseif (\RectorPrefix20211123\React\Dns\Model\Message::TYPE_MX === $type) {
            if ($rdLength > 2) {
                list($priority) = \array_values(\unpack('n', \substr($message->data, $consumed, 2)));
                list($target, $consumed) = $this->readDomain($message->data, $consumed + 2);
                $rdata = array('priority' => $priority, 'target' => $target);
            }
        } elseif (\RectorPrefix20211123\React\Dns\Model\Message::TYPE_SRV === $type) {
            if ($rdLength > 6) {
                list($priority, $weight, $port) = \array_values(\unpack('n*', \substr($message->data, $consumed, 6)));
                list($target, $consumed) = $this->readDomain($message->data, $consumed + 6);
                $rdata = array('priority' => $priority, 'weight' => $weight, 'port' => $port, 'target' => $target);
            }
        } elseif (\RectorPrefix20211123\React\Dns\Model\Message::TYPE_SSHFP === $type) {
            if ($rdLength > 2) {
                list($algorithm, $hash) = \array_values(\unpack('C*', \substr($message->data, $consumed, 2)));
                $fingerprint = \bin2hex(\substr($message->data, $consumed + 2, $rdLength - 2));
                $consumed += $rdLength;
                $rdata = array('algorithm' => $algorithm, 'type' => $hash, 'fingerprint' => $fingerprint);
            }
        } elseif (\RectorPrefix20211123\React\Dns\Model\Message::TYPE_SOA === $type) {
            list($mname, $consumed) = $this->readDomain($message->data, $consumed);
            list($rname, $consumed) = $this->readDomain($message->data, $consumed);
            if ($mname !== null && $rname !== null && isset($message->data[$consumed + 20 - 1])) {
                list($serial, $refresh, $retry, $expire, $minimum) = \array_values(\unpack('N*', \substr($message->data, $consumed, 20)));
                $consumed += 20;
                $rdata = array('mname' => $mname, 'rname' => $rname, 'serial' => $serial, 'refresh' => $refresh, 'retry' => $retry, 'expire' => $expire, 'minimum' => $minimum);
            }
        } elseif (\RectorPrefix20211123\React\Dns\Model\Message::TYPE_OPT === $type) {
            $rdata = array();
            while (isset($message->data[$consumed + 4 - 1])) {
                list($code, $length) = \array_values(\unpack('n*', \substr($message->data, $consumed, 4)));
                $value = (string) \substr($message->data, $consumed + 4, $length);
                if ($code === \RectorPrefix20211123\React\Dns\Model\Message::OPT_TCP_KEEPALIVE && $value === '') {
                    $value = null;
                } elseif ($code === \RectorPrefix20211123\React\Dns\Model\Message::OPT_TCP_KEEPALIVE && $length === 2) {
                    list($value) = \array_values(\unpack('n', $value));
                    $value = \round($value * 0.1, 1);
                } elseif ($code === \RectorPrefix20211123\React\Dns\Model\Message::OPT_TCP_KEEPALIVE) {
                    break;
                }
                $rdata[$code] = $value;
                $consumed += 4 + $length;
            }
        } elseif (\RectorPrefix20211123\React\Dns\Model\Message::TYPE_CAA === $type) {
            if ($rdLength > 3) {
                list($flag, $tagLength) = \array_values(\unpack('C*', \substr($message->data, $consumed, 2)));
                if ($tagLength > 0 && $rdLength - 2 - $tagLength > 0) {
                    $tag = \substr($message->data, $consumed + 2, $tagLength);
                    $value = \substr($message->data, $consumed + 2 + $tagLength, $rdLength - 2 - $tagLength);
                    $consumed += $rdLength;
                    $rdata = array('flag' => $flag, 'tag' => $tag, 'value' => $value);
                }
            }
        } else {
            // unknown types simply parse rdata as an opaque binary string
            $rdata = \substr($message->data, $consumed, $rdLength);
            $consumed += $rdLength;
        }
        // ensure parsing record data consumes expact number of bytes indicated in record length
        if ($consumed !== $expected || $rdata === null) {
            return null;
        }
        $message->consumed = $consumed;
        return new \RectorPrefix20211123\React\Dns\Model\Record($name, $type, $class, $ttl, $rdata);
    }
    private function readDomain($data, $consumed)
    {
        list($labels, $consumed) = $this->readLabels($data, $consumed);
        if ($labels === null) {
            return array(null, null);
        }
        // use escaped notation for each label part, then join using dots
        return array(\implode('.', \array_map(function ($label) {
            return \addcslashes($label, "\0.. .");
        }, $labels)), $consumed);
    }
    /**
     * @param string $data
     * @param int    $consumed
     * @param int    $compressionDepth maximum depth for compressed labels to avoid unreasonable recursion
     * @return array
     */
    private function readLabels($data, $consumed, $compressionDepth = 127)
    {
        $labels = array();
        while (\true) {
            if (!isset($data[$consumed])) {
                return array(null, null);
            }
            $length = \ord($data[$consumed]);
            // end of labels reached
            if ($length === 0) {
                $consumed += 1;
                break;
            }
            // first two bits set? this is a compressed label (14 bit pointer offset)
            if (($length & 0xc0) === 0xc0 && isset($data[$consumed + 1]) && $compressionDepth) {
                $offset = ($length & ~0xc0) << 8 | \ord($data[$consumed + 1]);
                if ($offset >= $consumed) {
                    return array(null, null);
                }
                $consumed += 2;
                list($newLabels) = $this->readLabels($data, $offset, $compressionDepth - 1);
                if ($newLabels === null) {
                    return array(null, null);
                }
                $labels = \array_merge($labels, $newLabels);
                break;
            }
            // length MUST be 0-63 (6 bits only) and data has to be large enough
            if ($length & 0xc0 || !isset($data[$consumed + $length - 1])) {
                return array(null, null);
            }
            $labels[] = \substr($data, $consumed + 1, $length);
            $consumed += $length + 1;
        }
        return array($labels, $consumed);
    }
}
