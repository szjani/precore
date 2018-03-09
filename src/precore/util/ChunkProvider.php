<?php
declare(strict_types=1);

namespace precore\util;

/**
 * Provides the proper chunk of data according the given offset.
 *
 * @see BufferedIterable
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface ChunkProvider
{
    /**
     * Returns the data chunk.
     *
     * @param $offset
     * @return Optional of an \Iterator
     */
    public function getChunk($offset) : Optional;
}
