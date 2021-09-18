/*!
	wow.export (https://github.com/Kruithne/wow.export)
	Authors: Kruithne <kruithne@gmail.com>
	License: MIT
 */

const BLTE_MAGIC = 0x45544c42;
const ENC_TYPE_SALSA20 = 0x53;
const EMPTY_HASH = '00000000000000000000000000000000';

class BLTEReader {
    /**
	 * Check if the given data is a BLTE file.
	 * @param {BufferWrapper} data 
	 */
    static check(data) {
        if (data.byteLength < 4)
            return false;

        const magic = data.readUInt32();
        data.seek(0);

        return magic === BLTE_MAGIC;
    }
    /**
	 * Construct a new BLTEReader instance.
	 * @param {BufferWrapper} buf 
	 * @param {string} hash 
	 * @param {boolean} partialDecrypt
	 */
    constructor(buf, hash, partialDecrypt = false) {
        this._blte = new Bufo(buf);
        this._ofs = 0;
        this.blockIndex = 0;
        this.blockWriteIndex = 0;
        this.partialDecrypt = partialDecrypt;

        const size = this._blte.byteLength;
        if (size < 8)
            throw new Error('[BLTE] Not enough data (< 8)');

        const magic = this._blte.readUInt32();
        if (magic !== BLTE_MAGIC)
            throw new Error('[BLTE] Invalid magic: ' + magic);

        const headerSize = this._blte.readInt32BE();
        const origPos = this._blte.offset;

        this._blte.seek(0);

        // let hashCheck = headerSize > 0 ? buf.readBuffer(headerSize).calculateHash() : buf.calculateHash();
        // if (hashCheck !== hash)
        //     throw new Error(util.format('[BLTE] Invalid MD5 hash, expected %s got %s', hash, hashCheck));

        this._blte.seek(origPos);
        let numBlocks = 1;

        if (headerSize > 0) {
            if (size < 12)
                throw new Error('[BLTE] Not enough data (< 12)');

            const fc = this._blte.readUInt8(4);
            numBlocks = fc[1] << 16 | fc[2] << 8 | fc[3] << 0;

            if (fc[0] !== 0x0F || numBlocks === 0)
                throw new Error('[BLTE] Invalid table format.');

            const frameHeaderSize = 24 * numBlocks + 12;
            if (headerSize !== frameHeaderSize)
                throw new Error('[BLTE] Invalid header size.');

            if (size < frameHeaderSize)
                throw new Error('[BLTE] Not enough data (frameHeader).');
        }

        this.blocks = new Array(numBlocks);
        let allocSize = 0;

        for (let i = 0; i < numBlocks; i++) {
            const block = {};
            if (headerSize !== 0) {
                block.CompSize = this._blte.readInt32BE();
                block.DecompSize = this._blte.readInt32BE();
                block.Hash = this._blte.readHexString(16);
            } else {
                block.CompSize = size - 8;
                block.DecompSize = size - 9;
                block.Hash = EMPTY_HASH;
            }

            allocSize += block.DecompSize;
            this.blocks[i] = block;
        }

        // Output buffer
        this._buf = new Bufo(new ArrayBuffer(allocSize));
    }

    /**
	 * Process all BLTE blocks in the reader.
	 */
    processAllBlocks() {
        while (this.blockIndex < this.blocks.length)
            this._processBlock();
    }

    getOutputBuffer() {
        this._buf.seek(0);
        return this._buf.readArrayBuffer(this._buf.byteLength);
    }

    /**
	 * Process the next BLTE block.
	 */
    _processBlock() {
        // No more blocks to process.
        if (this.blockIndex === this.blocks.length)
            return false;

        console.log("Processing block " + this.blockIndex);

        const oldPos = this.offset;
        console.log(this);
        // this.seek(this.blockWriteIndex);

        const block = this.blocks[this.blockIndex];
        const bltePos = this._blte.offset;

        console.log(block);
        // if (block.Hash !== EMPTY_HASH) {
        //     const blockData = this._blte.readBuffer(block.CompSize);
        //     const blockHash = blockData.calculateHash();

        //     // Reset after reading the hash.
        //     this._blte.seek(bltePos);

        //     if (blockHash !== block.Hash)
        //         throw new Error("[BLTE] Invalid block data hash.");
        // }

        this._handleBlock(this._blte, bltePos + block.CompSize, this.blockIndex);
        this._blte.seek(bltePos + block.CompSize);

        this.blockIndex++;
        console.log(block.DecompSize);
        this.blockWriteIndex += block.DecompSize;

        // this._blte.seek(oldPos);
    }

    /**
	 * Handle a BLTE block.
	 * @param {BufferWrapper} block
	 * @param {number} blockEnd
	 * @param {number} index 
	 */
    _handleBlock(block, blockEnd, index) {
        const flag = block.readUInt8();
        switch (flag) {
        case 0x45: // Encrypted
            // try {
            //     const decrypted = this._decryptBlock(block, blockEnd, index);
            //     this._handleBlock(decrypted, decrypted.byteLength, index);
            // } catch (e) {
            //     if (e instanceof EncryptionError) {
            //         // Partial decryption allows us to leave zeroed data.
            //         if (this.partialDecrypt)
            //             this._ofs += blockEnd - block.offset;
            //         else
            //             throw e;
            //     }
            // }
            console.log("Encryption NYI");
            break;
			
        case 0x46: // Frame (Recursive)
            throw new Error('[BLTE] No frame decoder implemented!');

        case 0x4E: // Frame (Normal)
            this._writeBufferBLTE(block, blockEnd);
            break;

        case 0x5A: // Compressed
            this._decompressBlock(block, blockEnd, index);
            break;

        default:
            throw new Error('Unknown block: ' + flag);
        }
    }

    /**
	 * Decompress BLTE block.
	 * @param {BufferWrapper} data 
	 * @param {number} blockEnd
	 * @param {number} index 
	 */
    _decompressBlock(data, blockEnd, index) {
        console.log("zlib decomp not spported");
        const decomp = data.readArrayBuffer(blockEnd - data.offset);
        const expectedSize = this.blocks[index].DecompSize;

        // Reallocate buffer to compensate.
        if (decomp.byteLength > expectedSize)
            this.setCapacity(this.byteLength + (decomp.byteLength - expectedSize));

        this._writeBufferBLTE(decomp, decomp.byteLength);
    }

    // /**
    //  * Decrypt BLTE block.
    //  * @param {BufferWrapper} data 
    //  * @param {number} blockEnd
    //  * @param {number} index 
    //  */
    // _decryptBlock(data, blockEnd, index) {
    //     const keyNameSize = data.readUInt8();
    //     if (keyNameSize === 0 || keyNameSize !== 8)
    //         throw new Error('[BLTE] Unexpected keyNameSize: ' + keyNameSize);

    //     const keyNameBytes = new Array(keyNameSize);
    //     for (let i = 0; i < keyNameSize; i++)
    //         keyNameBytes[i] = data.readHexString(1);

    //     const keyName = keyNameBytes.reverse().join('');
    //     const ivSize = data.readUInt8();

    //     if (ivSize !== 4)
    //         throw new Error('[BLTE] Unexpected ivSize: ' + ivSize);

    //     const ivShort = data.readUInt8(ivSize);
    //     if (data.remainingBytes === 0)
    //         throw new Error('[BLTE] Unexpected end of data before encryption flag.');

    //     const encryptType = data.readUInt8();
    //     if (encryptType !== ENC_TYPE_SALSA20)
    //         throw new Error('[BLTE] Unexpected encryption type: ' + encryptType);

    //     for (let shift = 0, i = 0; i < 4; shift += 8, i++)
    //         ivShort[i] ^= (index >> shift) & 0xFF;

    //     const key = tactKeys.getKey(keyName);
    //     if (typeof key !== 'string')
    //         throw new EncryptionError(keyName);

    //     const nonce = [];
    //     for (let i = 0; i < 8; i++)
    //         nonce[i] = (i < ivShort.length ? ivShort[i] : 0x0);

    //     const instance = new Salsa20(nonce, key);
    //     return instance.process(data.readBuffer(blockEnd - data.offset));
    // }

    /**
	 * Write the contents of a buffer to this instance.
	 * Skips bound checking for BLTE internal writing.
	 * @param {BufferWrapper} buf 
	 * @param {number} blockEnd
	 */
    _writeBufferBLTE(buf, blockEnd) {
        // this.copy(this._buf, this._ofs, buf, blockEnd);

        // this._buf => dst
        // this._ofs => dstOffset
        // buf => src
        // blockEnd => srcOffset

            // src => buffer, dstOffset => offset

            //    writeArrayBuffer(buffer, offset, count) {
            //         count = count || buffer.byteLength;
            //         let view = new DataView(buffer, offset || 0, count);
            //         for (let i = 0; i < count; i++)
            //             this.writeUInt8(view.getUint8(i));
            //     }
        let view = new DataView(buf._buffer.buffer, this._ofs || 0, buf.byteLength);
        for (let i = 0; i < buf.byteLength; i++)
            this._buf.writeUInt8(view.getUint8(i));
        this._ofs += blockEnd;
    }

    copy(dst, dstOffset, src, srcOffset){
        dst.writeArrayBuffer(src, dstOffset);
        console.log(dst, dstOffset, src, srcOffset);
    }

    /**
	 * Check a given length does not exceed current capacity.
	 * @param {number} length 
	 */
    _checkBounds(length) {
        // Check that this read won't go out-of-bounds anyway.
        super._checkBounds(length);

        // Ensure all blocks required for this read are available.
        const pos = this.offset + length;
        while (pos > this.blockWriteIndex)
            this._processBlock();
    }

    /**
	 * Write the contents of this buffer to a file.
	 * Directory path will be created if needed.
	 * @param {string} file 
	 */
    async writeToFile(file) {
        this.processAllBlocks();
        await super.writeToFile(file);
    }

    /**
	 * Decode this buffer using the given audio context.
	 * @param {AudioContext} context 
	 */
    async decodeAudio(context) {
        this.processAllBlocks();
        return super.decodeAudio(context);
    }

    /**
	 * Assign a data URL for this buffer.
	 * @returns {string}
	 */
    getDataURL() {
        if (!this.dataURL) {
            this.processAllBlocks();
            return super.getDataURL();
        }

        return this.dataURL;
    }
}