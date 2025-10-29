import { useBlockProps, RichText } from '@wordpress/block-editor';
import { Button, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

function CharCounter( { current, max } ) {
    return <span className="char-counter">({current} / {max})</span>;
}

function FaqItem( { faq, index, onRemove, onChange, isRemoveDisabled } ) {
    const [ isOpen, setIsOpen ] = useState( true );

    const toggleSpoiler = () => {
        setIsOpen( ! isOpen );
    };

    return (
        <div className="bb-faq-item-editor">
            <div className="bb-faq-item-header">
                <h4
                    className="bb-faq-question-num"
                    onClick={ toggleSpoiler }
                >
                    {`Вопрос #${index + 1}`}
                </h4>
                <div className="bb-faq-header-buttons">
                    <Button
                        className="bb-faq-control-button"
                        icon={
                            isOpen ? 'arrow-up-alt2' : 'arrow-down-alt2'
                        }
                        onClick={ toggleSpoiler }
                        aria-label={ isOpen ? 'Свернуть' : 'Развернуть' }
                    />
                    <Button
                        className="bb-faq-control-button bb-faq-delete-button"
                        icon="no-alt"
                        onClick={ () => onRemove( index ) }
                        aria-label="Удалить вопрос"
                        disabled={ isRemoveDisabled }
                        isDestructive
                    />
                </div>
            </div>

            { isOpen && (
                <div className="bb-faq-item-content">
                    <TextControl
                        label={
                            <>
                                Вопрос
                                <CharCounter
                                    current={ faq.question.length }
                                    max={ 1000 }
                                />
                            </>
                        }
                        value={ faq.question }
                        onChange={ ( value ) =>
                            onChange( index, 'question', value )
                        }
                        placeholder="Задать вопрос"
                        maxLength={ 1000 }
                    />
                    <label className="bb-faq-answer-label">
                        Ответ
                        <CharCounter
                            current={ faq.answer.length }
                            max={ 3500 }
                        />
                    </label>
                    <RichText
                        tagName="div"
                        className="bb-faq-answer-input"
                        value={ faq.answer }
                        onChange={ ( value ) =>
                            onChange( index, 'answer', value )
                        }
                        placeholder="Здесь должен быть ответ на вопрос"
                        allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
                    />
                </div>
            ) }
        </div>
    );
}

export default function Edit( { attributes, setAttributes } ) {
    const blockProps = useBlockProps( { className: 'bb-faq-block' } );
    const { title, faqs } = attributes;

    const onChangeFaq = ( index, field, value ) => {
        const newFaqs = faqs.map( ( item, i ) =>
            i === index ? { ...item, [ field ]: value } : item
        );
        setAttributes( { faqs: newFaqs } );
    };

    const onRemoveFaq = ( index ) => {
        if ( faqs.length === 1 ) return;
        const newFaqs = faqs.filter( ( _, i ) => i !== index );
        setAttributes( { faqs: newFaqs } );
    };

    const onAddFaq = () => {
        setAttributes( {
            faqs: [ ...faqs, { question: '', answer: '' } ],
        } );
    };

    const isBlockValid =
        title &&
        title.trim().length > 0 &&
        title.trim().length <= 100 &&
        Array.isArray( faqs ) &&
        faqs.length > 0 &&
        faqs.every(
            ( faq ) =>
                faq.question &&
                faq.question.trim().length > 0 &&
                faq.question.trim().length <= 1000 &&
                faq.answer &&
                faq.answer.trim().length > 0 &&
                faq.answer.trim().length <= 3500
        );

    return (
        <div { ...blockProps }>
            <TextControl
                label={
                    <>
                        Заголовок блока
                        <CharCounter current={ title.length } max={ 100 } />
                    </>
                }
                value={ title }
                onChange={ ( value ) => setAttributes( { title: value } ) }
                placeholder="Часто задаваемые вопросы"
                maxLength={ 100 }
            />

            { Array.isArray( faqs ) &&
                faqs.map( ( faq, index ) => (
                    <FaqItem
                        key={ index }
                        faq={ faq }
                        index={ index }
                        onRemove={ onRemoveFaq }
                        onChange={ onChangeFaq }
                        isRemoveDisabled={ faqs.length === 1 }
                    />
                ) ) }

            <div className="bb-faq-buttons-wrapper">
                <Button variant="secondary" onClick={ onAddFaq }>
                    Добавить вопрос
                </Button>
                <Button
                    variant="primary"
                    isPrimary
                    disabled={ ! isBlockValid }
                >
                    Сохранить
                </Button>
            </div>
        </div>
    );
}
